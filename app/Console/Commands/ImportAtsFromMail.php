<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PartsImport;
use App\Models\Part;
use Illuminate\Support\Facades\Log;

class ImportAtsFromMail extends Command
{
    protected $signature = 'mail:import-ats';
    protected $description = 'Import ATS supplier parts from Yandex Mail XLS attachment';

    public function handle()
    {
        $hostname = '{imap.yandex.ru:993/imap/ssl}INBOX';
        $username = 'sales@izicar.ru';
        $password = 'kvtslpctusalpxos'; // App password

        $inbox = @imap_open($hostname, $username, $password);

        if (!$inbox) {
            $this->error('Cannot connect to Yandex: ' . imap_last_error());
            return;
        }

        // Get today’s date in correct IMAP format
        $today = date('d-M-Y'); // e.g. 13-Oct-2025

        // 🔹 Search for emails from specific sender received today
        $searchQuery = 'FROM "optprice@ats-auto.ru" SINCE "' . $today . '"';
        $emails = imap_search($inbox, $searchQuery);

        if (!$emails) {
            $this->info('No new emails from optprice@ats-auto.ru today.');
            imap_close($inbox);
            return;
        }

        foreach ($emails as $email_number) {
            $overview = imap_fetch_overview($inbox, $email_number, 0)[0];
            $structure = imap_fetchstructure($inbox, $email_number);

            $date = date('Y-m-d H:i:s', strtotime($overview->date ?? ''));
            $subject = isset($overview->subject) ? imap_utf8($overview->subject) : '(no subject)';
            $from = imap_utf8($overview->from ?? '');

            $this->info("📧 Checking: {$subject} — {$date}");

            if (!isset($structure->parts)) continue;

            foreach ($structure->parts as $i => $part) {
                $isAttachment = false;
                $filename = '';

                // Check attachment name
                if ($part->ifdparameters) {
                    foreach ($part->dparameters as $object) {
                        if (strtolower($object->attribute) == 'filename') {
                            $isAttachment = true;
                            $filename = imap_utf8($object->value);
                        }
                    }
                }

                // Skip if not attachment
                if (!$isAttachment) continue;

                // Check file name conditions
                $isXls = preg_match('/\.(xls|xlsx)$/i', $filename);
                // $hasPrice = mb_stripos($filename, 'прайс') !== false;
                if (!$isXls) continue;
                
                $this->info("📎 Found valid attachment: {$filename}");

                // Decode the attachment
                $attachment = imap_fetchbody($inbox, $email_number, $i + 1);
                switch ($part->encoding) {
                    case 3:
                        $attachment = base64_decode($attachment);
                        break;
                    case 4:
                        $attachment = quoted_printable_decode($attachment);
                        break;
                }

                // Write to temp file
                $temp = tmpfile();
                fwrite($temp, $attachment);
                $meta = stream_get_meta_data($temp);
                $path = $meta['uri'];

                // ✅ Import directly
                try {
                    Part::truncate();
                    Excel::import(new PartsImport, $path);
                    $this->info("✅ Imported: {$filename}");
                } catch (\Throwable $e) {
                    $this->error("❌ Import failed: " . $e->getMessage());
                }

                fclose($temp);
            }

            // Mark as read
            imap_setflag_full($inbox, $email_number, "\\Seen");
        }

        imap_close($inbox);
        $this->info('All done!');






    }
}
// php artisan mail:import-ats