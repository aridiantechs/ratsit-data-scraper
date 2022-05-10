<?php

error_reporting(0);

include_once('simple_html_dom.php');
require_once (__DIR__ . '/vendor/autoload.php');
use Rct567\DomQuery\DomQuery;
use HeadlessChromium\BrowserFactory;

    function get_web_page( $url )
    {
        $user_agent = 'Mozilla/5.0 (Windows NT 6.1; rv:8.0) Gecko/20100101 Firefox/8.0';

        $options = array(
    
            CURLOPT_CUSTOMREQUEST  => "GET",        //set request type post or get
            CURLOPT_POST           => false,        //set to GET
            CURLOPT_USERAGENT      => $user_agent, //set user agent
            CURLOPT_COOKIEFILE     => "cookie.txt", //set cookie file
            CURLOPT_COOKIEJAR      => "cookie.txt", //set cookie jar
            CURLOPT_RETURNTRANSFER => true,     // return web page
            CURLOPT_HEADER         => false,    // don't return headers
            CURLOPT_FOLLOWLOCATION => true,     // follow redirects
            CURLOPT_ENCODING       => "",       // handle all encodings
            CURLOPT_AUTOREFERER    => true,     // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
            CURLOPT_TIMEOUT        => 120,      // timeout on response
            CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
            CURLOPT_PROXY          => 'zproxy.lum-superproxy.io',
            CURLOPT_PROXYPORT      => '22225',
            CURLOPT_PROXYUSERPWD   => 'lum-customer-hl_fa848026-zone-daniel_sahlin_zone-country-se:0xwx5ytxlfcc',
            CURLOPT_HTTPPROXYTUNNEL=> 1,
        );
        
        $ch      = curl_init( $url );
        curl_setopt_array( $ch, $options );
        $content = curl_exec( $ch );
        $err     = curl_errno( $ch );
        $errmsg  = curl_error( $ch );
        $header  = curl_getinfo( $ch );
        curl_close( $ch );

        $header['errno']   = $err;
        $header['errmsg']  = $errmsg;
        $header['content'] = $content;
        return $header;
    }



    function getDataWithAPI( $url )
    {
        $user_agent = 'Mozilla/5.0 (Windows NT 6.1; rv:8.0) Gecko/20100101 Firefox/8.0';

        $options = array(
    
            CURLOPT_CUSTOMREQUEST  => "GET",        //set request type post or get
            CURLOPT_POST           => true,        //set to GET
            CURLOPT_USERAGENT      => $user_agent, //set user agent
            CURLOPT_COOKIEFILE     =>"cookie.txt", //set cookie file
            CURLOPT_COOKIEJAR      =>"cookie.txt", //set cookie jar
            CURLOPT_RETURNTRANSFER => true,     // return web page
            CURLOPT_FOLLOWLOCATION => true,     // follow redirects
            CURLOPT_ENCODING       => "",       // handle all encodings
            CURLOPT_AUTOREFERER    => true,     // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
            CURLOPT_TIMEOUT        => 120,      // timeout on response
            CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
            // CURLOPT_PROXY          => 'zproxy.lum-superproxy.io',
            // CURLOPT_PROXYPORT      => '22225',
            // CURLOPT_PROXYUSERPWD   => 'lum-customer-hl_fa848026-zone-daniel_sahlin_zone:0xwx5ytxlfcc',
            // CURLOPT_HTTPPROXYTUNNEL=> 1,
            // CURLOPT_HTTPHEADER     => array(
            //                             'origin: https://www.ratsit.se',
            //                             'Content-Type: application/json',
            //                         ),

        );
        
        $ch = curl_init( $url );
        curl_setopt_array( $ch, $options );

        $result = curl_exec( $ch );
        $err     = curl_errno( $ch );
        $errmsg  = curl_error( $ch );
        $header  = curl_getinfo( $ch );
        curl_close( $ch );

        $data = json_decode($result, true);


        if(is_array($data)){
            if(array_key_exists('person', $data))
                if(array_key_exists('list', $data['person']))
                    if(!empty($data['person']['list']))
                        return $data['person']['list'][0];
            else
                return false;
        }
        else{
            return false;
        }
        
    }


    function headLessRequest($url){

        $browserCommand = 'google-chrome';

        $browserFactory = new BrowserFactory($browserCommand);
        $browser = $browserFactory->createBrowser([
                    'customFlags' => ['--no-sandbox'],
                ]);

        try {
            // creates a new page and navigate to an url
            $page = $browser->createPage();
            $page->navigate($url)->waitForNavigation();

            return $page->getHtml();
        }
        finally {
            $browser->close();
        }
    }

    function putTestHtml($html = '')
    {
        file_put_contents("uploads/html.txt", "");

        $myfile = fopen('./uploads/'.'html'.'.txt', "a") or die("Unable to open file!");
        $txt = $html;
        fwrite($myfile, $txt);
        fclose($myfile);
    }


    function getData($address,$key,$file_name)
    {

        $original_input = $address = trim($address);

        $url = 'https://www.ratsit.se/api/search/person?vem='.$address.'&var=&m=1&k=1&r=1&er=1&b=1&eb=1&amin=16&amax=120&fon=1&typ=1&page=1';

        $result = getDataWithAPI($url);
        
        $first_name = $last_name = $address = $age = $postort = $post = $details = $change_date = $pnr = '';

        if($result){
            
            createLog($key, $original_input, $url, true);

            foreach ($result['names'] as $key => $value)
                $first_name .= $value . ' ';
            
            $last_name = $result['lastNameComplete'];
            $address     = $result['address'];
            $age         = $result['age'];
            $postort     = $result['postort'];
            $post        = $result['postNr'];
            $details_url = $result['personrapportUrl'];


            $details_url = 'https://www.ratsit.se' . $details_url;


            $result = get_web_page(trim($details_url));
            $html   = $result['content'];
            $dom    = str_get_html($html);

            if(gettype($dom) !== 'boolean'){

                $change_date = $dom->find('.rapport__list', 1);
                
                if(!is_null($change_date) || !empty($change_date))
                    $change_date = $change_date->find('dd', 0)->plaintext;
                else
                    $change_date = '';

                $pnr = $dom->find('.rapport__pnr', 0);
                if(!is_null($pnr) || !empty($pnr))
                    $pnr = $pnr->find('span', 0)->plaintext;
                else
                    $pnr = '';
                                
            }
            else{
            
                handleFailedAddresses($dom, $html, $key, $original_input);
                return;

            }

        }

        // Store data
        if(!$result){
            createLog($key,$original_input,'Link issue');

            // Enter not found data
        }
        else{

            if(1){
                $myfile = fopen('./uploads/'.$file_name.'.txt', "a") or die("Unable to open file!");
                $txt =  trim($original_input)     . "\t" .
                        trim($first_name)     . "\t" . 
                        trim($last_name)      . "\t". 
                        trim($address)  . "\t". 
                        trim($age)   . "\t". 
                        trim($postort)     . "\t". 
                        trim($post)   . "\t". 
                        trim($pnr)   . "\t". 
                        trim($change_date);

                fwrite($myfile, $txt);
                fwrite($myfile, "\n");
                fclose($myfile);
            }

        }

    }

    function createLog($key,$address,$page_link, $address_found = false){
        
        $myfile = fopen('./logs/log.txt', "a") or die("Unable to open file!");

        $txt = $key . ' - ' . $address . ' - ' .  $page_link;

        fwrite($myfile, $txt);
        fwrite($myfile, "\n");
        fclose($myfile);

        // End Log

        if(!$address_found){

            $myfile  = fopen('./logs/failed.txt', "a") or die("Unable to open file!");

            fwrite($myfile, urldecode($address));
            fwrite($myfile, "\n");
            fclose($myfile);

            $myfile  = fopen('./logs/failed-log.txt', "a") or die("Unable to open file!");

            $address = $address . ',';
            fwrite($myfile, $address);
            fwrite($myfile, "\n");
            fclose($myfile);            

        }
    }

    function handleFailedAddresses($dom, $html, $key, $address){

        foreach($dom->find('.h2') as $element){
            
            if($element == '<h2 class="h2"> Ingen träff </h2>'){
                createLog($key,$address,'Address not found');
                return;
            }

        }

        $dom = new DomQuery($html);
        if($dom->find('h1') == '<h1 data-translate="turn_on_js" style="color:#bd2426;">Please turn JavaScript on and reload the page.</h1><h1><span data-translate="checking_browser">Checking your browser before accessing</span> merinfo.se.</h1>'){
            
            createLog($key,$address,'Javascript error');
            return;

        }
        else if($dom->find('a') == '<a rel="noopener noreferrer" href="https://www.cloudflare.com/5xx-error-landing/" target="_blank">Cloudflare</a>'){

            createLog($key,$address,'Cloudflare error');
            return;
            
        }
        else{

            createLog($key,$address,'Unknown Error');

        }

    }

    if (1) {
        
        $file_name = "final";
        
        $file = fopen('uploads/'.$file_name.'.txt', "w");
        
        fclose($file);

        $file_addresses = fopen("source/input.txt", "r") or die("Unable to open file!");

        $addresses = [];

        while (($line = fgets($file_addresses)) !== false)
            $addresses[] = $line;


        foreach(array_unique($addresses) as $key => $address)
            getData($address, $key, $file_name);

    }