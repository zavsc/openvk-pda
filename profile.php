<?php
ini_set('display_errors', 0);
date_default_timezone_set('Europe/Moscow');
session_start();
if(empty($_SESSION['access_token'])){
 header('Location: /');
 die();
}
$access_token = $_SESSION['access_token'];
$instance = $_SESSION['instance'];
?>
<title>OpenVK PDA</title>
<meta charset="utf-8">
<style>
.td {
  padding: 10px;
  padding-left: 20px;
}
</style>
<table width="100%" border="0" cellspacing="0" cellpadding="4" bgcolor="#808080">
<tbody>
<tr>
<td nowrap="" width="40%">
<b>
<font color="#ffffff">OpenVK PDA</font>
<font color="#ffffff"> | <a href="/profile.php">Профиль</a> | <a href="/post.php">Запостить</a> | <a href="/logout.php">Выйти</a></font>
<font color="#ffffff" style="display: none;"><?php echo $access_token; ?></font> <!-- Токен пользователя -->
</b>
</td>
</tr>
</tbody>
</table>
<?php
$service_url = "https://$instance/method/Account.getProfileInfo?&access_token=$access_token";
$curl = curl_init($service_url);
curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0)");
curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
$curl_response = curl_exec($curl);
curl_close($curl);
$curl_json = json_decode($curl_response, true);
$userids = $curl_json['response']['id'];
$userget = "https://$instance/method/Users.get?&user_ids=$userids&fields=verified,sex,,photo_100,status";
$curluserget = curl_init($userget);
curl_setopt($curluserget, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0)");
curl_setopt($curluserget, CURLOPT_RETURNTRANSFER,true);
curl_setopt($curluserget, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($curluserget, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($curluserget, CURLOPT_SSL_VERIFYPEER, 0);
$curl_responseuget = curl_exec($curluserget);
curl_close($curluserget);
$curl_jsonuget = json_decode($curl_responseuget, true);
if ($curl_jsonuget['response'][0]['verified'] == '0') {
}
if ($curl_jsonuget['response'][0]['verified'] == '1') {
    $verify = '<td><img src="/checkmark.png"></td>';
    global $verify;
}
$urlava = $curl_jsonuget['response'][0]['photo_100'];
$pathava = './avatars/'.$curl_json['response']['id'].'.gif';
$_SESSION['ava'] = '/avatars/'.$curl_json['response']['id'].'.gif';
file_put_contents($pathava, file_get_contents($urlava));
$owner_id = $curl_json['response']['id'];
echo '
<table>
    <tr>
        <td style="width: 25%;">
            <img src="'.$_SESSION['ava'].'">
        </td>
        <td>
            <table>
                <tr>
                    <td>'.$curl_json['response']['first_name'].'</td>
                    <td>'.$curl_json['response']['last_name'].'</td>
                    '.$verify.' 
                </tr>
                <tr>
                    <td>'.$curl_json['response']['status'].'</td>
                    <td class="td">Город: '.$curl_json['response']['home_town'].'</td>
                    <td>Дата рождения: '.$curl_json['response']['bdate'].'</td>
                </tr>
            </table>
        <td>
    </tr>
</table>
';
?>
<hr>
<form action="user.php" method="post">
Открыть профиль другого пользователя <br>
ID... <input type="text" name="uid"><button type="submit" name="post">Открыть</button>
</form>
<hr>
<?php
$wallgeturl = "https://$instance/method/Wall.get?owner_id=$owner_id&access_token=$access_token";
$wallget = curl_init($wallgeturl);
curl_setopt($wallget, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0)");
curl_setopt($wallget, CURLOPT_RETURNTRANSFER,true);
curl_setopt($wallget, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($wallget, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($wallget, CURLOPT_SSL_VERIFYPEER, 0);
$wallget_response = curl_exec($wallget);
curl_close($wallget);
$wallget_json = json_decode($wallget_response, true);
$count = $wallget_json['response']['count'];
?>
<b>Стена</b> <?php echo $count; ?> записи.
<hr>
<form action="profile.php" method="post">
Написать... <input type="text" name="message"><button type="submit" name="post">Написать</button>
</form>
<?php
if(isset($_POST['post'])) {
$message = $_POST['message'];
$service_url1 = "https://$instance/method/Wall.post?&access_token=$access_token&owner_id=$owner_id&message=$message";
$curl1 = curl_init($service_url1);
curl_setopt($curl1, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0)");
curl_setopt($curl1, CURLOPT_RETURNTRANSFER,true);
curl_setopt($curl1, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($curl1, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($curl1, CURLOPT_SSL_VERIFYPEER, 0);
$curl_response1 = curl_exec($curl1);
curl_close($curl1);
$curl_json1 = json_decode($curl_response1, true);
echo '
  Запись опубликована:
  <table border="1">
   <tr>
    <th>Ссылка</th>
    <th>Запись</th>
   </tr>
   <tr>
   <td><a href="http://'.$instance.'/wall'.$owner_id.'_'.$curl_json1['response']['post_id'].'">http://'.$instance.'/wall'.$owner_id.'_'.$curl_json1['response']['post_id'].'</a></td>
   <td>'.$message.'</td>
   </tr>
  </table>
  <hr>
';
}
?>
<?php
foreach ($wallget_json['response']['items'] as $text) {
    $timestamp = $text['date'];
    $date = date("d.m.Y H:i", $timestamp);
    echo '
    <table border="1">
    <tr>
    <th>Запись</th>
    <th>Дата</th>
    </tr>
    <tr>
    <td>'.$text['text'].'</td>
    <td>'.$date.'</td>
    </tr>
    </table>
    <br>
    ';
}
?>