<?php
  require_once('env.php');

  function getAccessToken($wxClientId, $wxClientSecret, $wxRedirectUri) {
    $code = $_GET['code'];
    if (isset($code)) { 
      // Use cURL to get a new access token and refresh token
      $ch = curl_init();

      // create request URL 
      $url = 'https://webexapis.com/v1/access_token';
  
      $postBody = 'grant_type=authorization_code' .
        '&client_id=' . $wxClientId .
        '&client_secret=' . $wxClientSecret .
        '&code=' . $code .
        '&redirect_uri=' . $wxRedirectUri;
  
      curl_setopt_array($ch, array(
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/x-www-form-urlencoded',
            ),
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postBody,
        ));
  
      // Make the call
      $result = curl_exec($ch);
      curl_close($ch);
      var_dump(json_decode($result, true));
      return json_decode($result, true);
    }
  };

  function refreshToken($wxClientId, $wxClientSecret, $wxRefreshToken) {
    if ($wxRefreshToken) { 
      // Use cURL to get a new access token and refresh token
      $ch = curl_init();

      // create request URL 
      $url = 'https://webexapis.com/v1/access_token';
  
      $postBody = 'grant_type=refresh_token' .
        '&client_id=' . $wxClientId .
        '&client_secret=' . $wxClientSecret .
        '&refresh_token=' . $wxRefreshToken;
  
      curl_setopt_array($ch, array(
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/x-www-form-urlencoded',
            ),
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postBody,
        ));
  
      // Make the call
      $result = curl_exec($ch);
      curl_close($ch);
      var_dump(json_decode($result, true));
      return json_decode($result, true);
    }
  }
  
  // get token
  $token = getAccessToken($wxClientId, $wxClientSecret, $wxRedirectUri);

  if (isset($token['refresh_token']) && $token['expires_in'] < 5000) {
    $token = refreshToken($wxClientId, $wxClientSecret, $token['refresh_token']);
  }
  if (!empty($token) && isset($token['access_token'])) {
    $accessToken = $token['access_token'];

    if ($_POST && !empty($accessToken)) {
      var_dump($_POST);
      $ch = curl_init();
  
      $url = 'https://webexapis.com/v1/meetings';
  
      $headers = [
        'Accept: application/json',
        'Content-Type: application/json',
        "Authorization: Bearer $accessToken",
      ];
  
      $title = $_POST['title'] || '';
      $start = $_POST['start'] || '';
      $end = $_POST['end'] || '';
      $email = $_POST['email'] || '';
  
      $postBody = [
        'body' => [
          'adhoc' => false,
          'scheduleType' => 'meeting',
          'timezone' => 'America/Chicago',
          'title' => $title,
          'start' => $start,
          'end' => $end,
        ],
      ];
  
      if (isset($email)) {
        $postBody['body']['invitees'] = [
          array('email' => $email)
        ];
      }
  
      if ( isset($title) && isset($start) && isset($end) ) {
        curl_setopt_array($ch, array(
          CURLOPT_HTTPHEADER => $headers,
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_POST => true,
          CURLOPT_POSTFIELDS => json_encode($postBody),
          ));
    
        // Make the call
        $newMeeting = curl_exec($ch);
        curl_close($ch);
        var_dump($newMeeting);
      }
    }
  }

?>

<!DOCTYPE html>
<html lang="en">
  <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>WebEx Sample Integration</title>
      <style>
        input {
          margin-bottom: 20px;
        }
      </style>
  </head>
  <body>
    <h1>Sample WebEx App</h1>
    <a href="<?php echo $wxAuthUrl; ?>" target="_blank">Authorize WebEx</a>
    <h2>Create a Meeting</h2>
    <form 
      action="" method="POST" 
      style="display: flex; flex-direction: column;"
    >
      <label for="title">Meeting Title</label>
      <input type="text" name="title" id="title" required />
      <label for="start">Start Time</label>
      <input type="datetime-local" id="start" name="start" min="<?php echo date(DATE_ATOM); ?>" required />
      <label for="end">End Time</label>
      <input type="datetime-local" id="end" name="end" min="<?php echo date(DATE_ATOM, strtotime("+30 minutes")); ?>" max="<?php echo date(DATE_ATOM, strtotime("+1 hour")); ?>" required />
      <label for="email">Email Address to Invite</label>
      <input type="email" name="email" id="email" />
      <button type="submit">Create Meeting</button>
    </form>


    <script type="text/javascript">
      const startTime = document.getElementById('start').value
      const endInput = document.getElementById('end')
      let d = new Date(startTime)
      const startPlus30Min = d.setHours(d.getMinutes() + 30)
      const startPlus1Hr = d.setHours(d.getHours() + 1)
      endInput.setAttribute('min', startPlus30Min) 
      endInput.setAttribute('max', startPlus1Hr)
    </script> 

  </body>
</html>