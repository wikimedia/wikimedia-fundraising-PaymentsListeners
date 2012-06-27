<?php

    /**
     * Connect to a URL, send optional post variables, return data
     *
     * Yoinked from _fundcore_paypal_download in fundcore/gateways/fundcore_paypal.module Drupal module
     * @param $url String of the URL to connect to
     * @param $vars Array of POST variables
     * @return String containing the output returned from Server
     */
    public function curl_download( $url, $vars = NULL ) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0); 
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
            
        if ($vars !== NULL) {
            curl_setopt($ch, CURLOPT_POST, 1); 
            curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
        }
        
        $i = 0;
        
        while (++$i <= 3){
            $data = curl_exec($ch);
            $header = curl_getinfo($ch);
            
            if ( $header['http_code'] != 200 && $header['http_code'] != 403 ){
                //paypal blow'd up.
                sleep( 1 );
            }
            
            if (!$data) {
                $data = curl_error($ch);
                Logger::log( "Curl error: " . $data );
            } else {
                break;
            }
            
        }
        curl_close($ch);
        return $data;
    }
