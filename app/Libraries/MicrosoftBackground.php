<?php

namespace App\Libraries;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseRedirect;


class MicrosoftBackground
{
    const CLIENT_ID = '1005b077-29ab-45fa-8eb5-08fff368995d';
    const CLIENT_SECRET = 'wRF8Q~f5MXYUjEvqgdABAHh3vTxIl2RLGQl6icdr';
    const TOKEN_URL = 'https://login.microsoftonline.com/d5abce6f-1b14-47d0-b79e-83fc8f87c81e/oauth2/v2.0/token';
    const DAEMON_URL = 'https://graph.microsoft.com/v1.0/users';
    const API_URL =  'https://graph.microsoft.com/v1.0/users/{0538e8b0-9f0a-4db3-a362-5e9492419046}/messages';
    const ATTACH_URL = 'https://graph.microsoft.com/v1.0/users/{0538e8b0-9f0a-4db3-a362-5e9492419046}/messages/AAMkADJkZTg1MmFlLTIyZjYtNDE2OS1hODYxLTI1MWJjY2U2MTliYgBGAAAAAACjeROPwHerS5IPqILIeoC5BwBoV3_qFoXWTp_5_BJhWDJzAAABZ7EHAABoV3_qFoXWTp_5_BJhWDJzAAABaFoCAAA=/attachments/AAMkADJkZTg1MmFlLTIyZjYtNDE2OS1hODYxLTI1MWJjY2U2MTliYgBGAAAAAACjeROPwHerS5IPqILIeoC5BwBoV3_qFoXWTp_5_BJhWDJzAAABZ7EHAABoV3_qFoXWTp_5_BJhWDJzAAABaFoCAAABEgAQACNV8fCMViFAvrMtakQ0qFE=/$value';
   
    
// messageid: AAMkADJkZTg1MmFlLTIyZjYtNDE2OS1hODYxLTI1MWJjY2U2MTliYgBGAAAAAACjeROPwHerS5IPqILIeoC5BwBoV3_qFoXWTp_5_BJhWDJzAAABZ7EHAABoV3_qFoXWTp_5_BJhWDJzAAABaFoCAAA=
// userid: 0538e8b0-9f0a-4db3-a362-5e9492419046
// eyJ0eXAiOiJKV1QiLCJub25jZSI6IkkzNWxjUjFfVVl2VGNFUEdDbk9YVzZQLWRtc1pMMnNMZlBENjRDLWxTSEUiLCJhbGciOiJSUzI1NiIsIng1dCI6InEtMjNmYWxldlpoaEQzaG05Q1Fia1A1TVF5VSIsImtpZCI6InEtMjNmYWxldlpoaEQzaG05Q1Fia1A1TVF5VSJ9.eyJhdWQiOiJodHRwczovL2dyYXBoLm1pY3Jvc29mdC5jb20iLCJpc3MiOiJodHRwczovL3N0cy53aW5kb3dzLm5ldC9kNWFiY2U2Zi0xYjE0LTQ3ZDAtYjc5ZS04M2ZjOGY4N2M4MWUvIiwiaWF0IjoxNzE0MTMxNTAzLCJuYmYiOjE3MTQxMzE1MDMsImV4cCI6MTcxNDEzNTQwMywiYWlvIjoiRTJOZ1lFaWFYSjkrc2xxL3ZPWHRqN2ozNjd6dUFRQT0iLCJhcHBfZGlzcGxheW5hbWUiOiJDcm9uSm9icyIsImFwcGlkIjoiMTAwNWIwNzctMjlhYi00NWZhLThlYjUtMDhmZmYzNjg5OTVkIiwiYXBwaWRhY3IiOiIxIiwiaWRwIjoiaHR0cHM6Ly9zdHMud2luZG93cy5uZXQvZDVhYmNlNmYtMWIxNC00N2QwLWI3OWUtODNmYzhmODdjODFlLyIsImlkdHlwIjoiYXBwIiwib2lkIjoiYjUwNGZjY2YtNmM2OC00NmQ5LTlhOWQtYmMxZjA5YjFhZDMwIiwicmgiOiIwLkFSSUFiODZyMVJRYjBFZTNub1A4ajRmSUhnTUFBQUFBQUFBQXdBQUFBQUFBQUFEV0FBQS4iLCJyb2xlcyI6WyJNYWlsLlJlYWRXcml0ZSIsIkZpbGVzLlJlYWRXcml0ZS5BbGwiLCJNYWlsLlNlbmQiXSwic3ViIjoiYjUwNGZjY2YtNmM2OC00NmQ5LTlhOWQtYmMxZjA5YjFhZDMwIiwidGVuYW50X3JlZ2lvbl9zY29wZSI6Ik5BIiwidGlkIjoiZDVhYmNlNmYtMWIxNC00N2QwLWI3OWUtODNmYzhmODdjODFlIiwidXRpIjoiaVJfT1FGcDhrMC1NcGRkb1psY1VBUSIsInZlciI6IjEuMCIsIndpZHMiOlsiMDk5N2ExZDAtMGQxZC00YWNiLWI0MDgtZDVjYTczMTIxZTkwIl0sInhtc190Y2R0IjoxMzk3NjU2MTg2fQ.tTB1NaqMDNBSIFge_Zv1hm8uyP-8GZz60e2LSyd3M6x0LRakfhhDjw2gHgh3kN3GdOpRX_QWrWEOuClWS33NhuprpzJkzWTyuMUk_SWsiZtltx8FiG8yqj8-VWiVgyz0w_5HBqV-CyVvxc0rMqF06-JXU24HMtqdiUq62gIwR27c7uwbSrc2bVQ2cpk69WB3903PzUkBaAwmv0yWl7iCBHxcELN1HoTGWgp0RJ-I7_k9kmhDJG2a4a1f9gKK3Vmj7RcgX8-XuJsrP9wp4qkavZu-sgrKfTFiKE5wcJjMQxVadW3qG-UTXqc-hi_o2qO4g59647uXW75xXK3E22pa0g

function access_token() {
  // Exchange the auth code for a token
  $ch = curl_init(self::TOKEN_URL);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'client_id' => self::CLIENT_ID,
    'scope' => 'https://graph.microsoft.com/.default',
    'client_secret' => self::CLIENT_SECRET,
    'grant_type' => 'client_credentials',
  ]));
  $raw = curl_exec($ch);
  $data = json_decode($raw, true);
  $access_token = $data['access_token'];
  return $access_token;
 }


function get_message($access_token) {
    
 $params = array(
    '$search' => 'Docuware',
    '$top' => 1
  );
  
  $options = http_build_query($params);
  $search_messages_url = self::API_URL . '?' . $options;
  // Exchange the auth code for a token
  $ch = curl_init($search_messages_url);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer ' . $access_token));
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $raw = curl_exec($ch);
  $message = json_decode($raw, true);
  return $message;
 }
 
 function get_attachment($access_token) {
  $ch = curl_init(self::ATTACH_URL);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer ' . $access_token));
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $raw = curl_exec($ch);
  $attachment = json_decode($raw, true);
  return $attachment; 
 }
 
 /*
 function get_userids($access_token) {
    // Exchange the auth code for a token
  $ch = curl_init(self::DAEMON_URL);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer ' . $access_token));
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $raw = curl_exec($ch);
  $data = json_decode($raw, true);
  return $data;
}
  */  

    
    
}
