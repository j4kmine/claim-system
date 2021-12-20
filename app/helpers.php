<?php

use App\Mail\Email;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\Company;

if (!function_exists('insurance_api')) {
    function insurance_api($endpoint, $body, $post = true)
    {
        $client = new \GuzzleHttp\Client([
            'headers' => [
                'Authorization' => 'Basic ' . env('INSURANCE_TOKEN'),
                'Content-Type' => 'application/json'
            ]
        ]);
        if ($post) {
            return $client->post($endpoint, [
                'json' => $body
            ]);
        } else {
            return $client->get($endpoint);
        }
    }
}

if (!function_exists('insurance_file')) {
    function insurance_file($endpoint, $body)
    {
        $client = new \GuzzleHttp\Client([
            'headers' => [
                'Authorization' => 'Basic ' . env('INSURANCE_TOKEN')
            ]
        ]);
        return $client->post($endpoint, [
            'sink' => $body
        ]);
    }
}

if (!function_exists('email')) {
    function email($claim, $template, $subject, $role, $company_id, $files = null)
    {
        if ($role != null) {
            $users = User::where('role', $role)->where('status', 'active')->where('company_id', $company_id)->where('notification_email', '1')->get();
        } else {
            $users = User::where('company_id', $company_id)->where('status', 'active')->where('notification_email', '1')->get();
        }

        if ($company_id != null) {
            $name = Company::where('id', $company_id)->first()->name;
        } else {
            $name = 'AllCars';
        }

        foreach ($users as $user) {
            Mail::to($user->email)->queue(new Email($name, $subject, $claim, $template, $files));
        }
    }
}

if (!function_exists('unslugify')) {
    function unslugify($str)
    {
        return ucwords(str_replace("_", " ", $str));
    }
}

//for debugging
if (!function_exists('de')) {
    function de($data)
    {
        response()->json($data)->send();

        die;
    }
}


if (!function_exists('mime_type')) {

    function mime_type($filename)
    {

        $mime_types = array(

            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        } elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $mimetype;
        } else {
            return 'application/octet-stream';
        }
    }
}
