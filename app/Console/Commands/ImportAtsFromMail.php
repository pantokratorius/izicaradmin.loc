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
        $username = 'erik.krasnauskas@yandex.ru';
        $password = 'rlsdtwetpaokoktu'; // use app password if 2FA is on


        $inbox = @imap_open($hostname, $username, $password);
        if (!$inbox) {
            $this->error('Cannot connect to Yandex: ' . imap_last_error());
            return;
        }

        // Find unread messages with attachments
        $emails = imap_search($inbox, 'UNSEEN');
        if (!$emails) {
            $this->info('No new emails with attachments.');
            imap_close($inbox);
            return;
        }

        foreach ($emails as $email_number) {
            $structure = imap_fetchstructure($inbox, $email_number);

            if (!isset($structure->parts)) continue;

            foreach ($structure->parts as $i => $part) {
                $isAttachment = false;
                $filename = '';

                if ($part->ifdparameters) {
                    foreach ($part->dparameters as $object) {
                        if (strtolower($object->attribute) == 'filename') {
                            $isAttachment = true;
                            $filename = $object->value;
                        }
                    }
                }
                if (!$isAttachment) continue;
                
                // Process only .xls or .xlsx
                // if (!preg_match('/\.(xls|xlsx)$/i', $filename)) continue;
                $attachment = imap_fetchbody($inbox, $email_number, $i + 1);
                switch ($part->encoding) {
                    case 3:  $attachment = base64_decode($attachment); break;
                    case 4:  $attachment = quoted_printable_decode($attachment); break;
                }
                
                // Log::info(print_r($attachment, 1));
                // Convert binary data to a temporary in-memory stream
                $temp = tmpfile();
                fwrite($temp, $attachment);
                $meta = stream_get_meta_data($temp);
                $path = $meta['uri'];

                Part::truncate();
                // Import using Maatwebsite Excel directly from stream
                Excel::import(new PartsImport, $path);

                fclose($temp);

                $this->info("✅ Imported: {$filename}");
            }

            // Mark message as seen
            imap_setflag_full($inbox, $email_number, "\\Seen");
        }

        imap_close($inbox);
        $this->info('All done!');
    }
}
// php artisan mail:import-ats