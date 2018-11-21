<?php
/**
 * Copyright 2018 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
// [START drive_quickstart]
require __DIR__ . '/vendor/autoload.php';
session_start();
ini_set('display_errors', 1);
// if (php_sapi_name() != 'cli') {
//     throw new Exception('This application must be run on the command line.');
// }
/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */ 
    $client = new Google_Client();
    $client->setApplicationName('Google Drive API PHP Quickstart');
    $client->setScopes(Google_Service_Drive::DRIVE_METADATA_READONLY);
    $client->setAuthConfig('credentials.json'); 
    $client->setPrompt('select_account consent');
    $client->setDeveloperKey('###');
        

    $client->setScopes(array(
        'https://mail.google.com/',
        'https://www.googleapis.com/auth/gmail.compose',
        'https://www.googleapis.com/auth/drive',
        'https://www.googleapis.com/auth/drive.appdata',
        'https://www.googleapis.com/auth/drive.apps.readonly',
        'https://www.googleapis.com/auth/drive.file',
        'https://www.googleapis.com/auth/drive.metadata',
        'https://www.googleapis.com/auth/drive.metadata.readonly',
        'https://www.googleapis.com/auth/drive.photos.readonly',
        'https://www.googleapis.com/auth/drive.readonly'
    ));
    
    $service = new Google_Service_Drive($client); 
    
    if (isset($_REQUEST['logout'])) {
        unset($_SESSION['access_token']);
    }
    
    if (isset($_GET['code'])) {
    
        $client->authenticate($_GET['code']);
        $_SESSION['access_token'] = $client->getAccessToken();
    
        $url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
    
        header('Location: ' . filter_var($url, FILTER_VALIDATE_URL));
    }
    
    
    
    if (isset($_SESSION['access_token'])) {
    
    
        $client->setAccessToken($_SESSION['access_token']);
    
    } else {
        $loginUrl = $client->createAuthUrl(); 
    
        echo '<h1>Please <a href="' . $loginUrl . '">Login</a> into your account </h1>';
    }
    
    try {
        if (isset($_SESSION['access_token']) && $client->getAccessToken()) {
            if ($client->isAccessTokenExpired()) {
                $loginUrl = $client->createAuthUrl();
                echo 'Token Expired. ';
                echo '<br>Click <a href="' . $loginUrl . '">HERE</a> to re-login';
                exit();
            }
        
            ini_set('display_errors', 1);
            $format = new DateTime();
            $date = new DateTime('now');
            $date_str = $date->format('Y-m-d\TH:i:s') . substr((string) microtime(), 1, 4) . 'Z'; 
            $files = $service->files->listFiles([
                'q' => "modifiedTime < '$date_str'",
                'fields' => 'files'
            ]);
    
            if (count($files) == 0) {
    
                echo "<h3>   No files found.         </h3>";
            } else { 
                echo '<div style="background:#f4f4fd">';
                echo '<h3 style="text-align:right"><a href="/index.php?logout">LOGOUT</a></h3>'; 
    
                echo '<table border="1">';
                echo '<tr>
                <th>ID</th>
                <th>NAME</th>            
                <th>AUTHOR</th>            
                <th>CREATED AT</th>
                <th>MODIFIED AT</th>
                
                </tr>';
                foreach ($files['files'] as $file) {
                    echo'<tr>'
                    . '<td>' . $file['id'] . '</td>'
                    . '<td>' . $file['name'] . '</td>'
                    . '<td> ' . $file['owners'][0]['displayName']. '</td>'
                    . '<td>' . $file['createdTime'] . '</td>'
                    . '<td>' . $file['modifiedTime'] . '</td>'
                    . '</tr>';
                }
                echo '</table>';
            }
            echo '</div>'; 
            exit();
        }
    } catch (Google_Auth_Exception $e) {
        $loginUrl = $client->createAuthUrl();
        echo 'Error: ' . $e->getMessage;
        echo '<br>Click <a href="' . $loginUrl . '">HERE</a> to re-login';
    } 