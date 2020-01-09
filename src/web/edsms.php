<?php 

    // created by szkiddaj (https://github.com/szkiddaj/edsms-mtasa)
    // Ha kimered adni saját munkádnak, nyakonbaszlak

    // Szerver adatok, válasz SMS, stb...
    $dbHost = '127.0.0.1'; // Adatbázis IP címe
    $dbUser = 'root'; // Adatbázis felhasználó
    $dbPass = ''; // Adatbázis jelszó
    $dbTable = ''; // Adatbázisban lévő tábla neve

    $serverip = '127.0.0.1';
    $serverport = 22005; // HTTP PORT KELL IDE, MONDOM HTTP PORT
    $username = 'phpsdk';
    $password = 'asd123';
    $resourceName = 'edsms'; // Resource neve, amit meghív a kód
    $functionName = 'receiveDonation';

    $successfulMsg = 'Koszonjuk a tamogatasod!'; // Válasz sms (!!!Ékezeteket nem támogat!!!)
    $errorMsg = 'Sikertelen tamogatas! Kerlek keress fel egy tulajdonost!'; // Hiba válasz sms (!!!Ékezeteket nem támogat!!!)

    /*
        Lehetséges hibakódok amiket a támogató kaphat:

        #DB1 = Sikertelen csatlakozás az sqlhez. (Valószínűleg el lettek írva az adatok)
        #DB2 = Nem sikerült belerakni az sqlbe a támogatást.
        #GAME1 = Ha valamiért nem tudja meghívni a függvényt a szerveren (Nincs elindítva, nincsen joga a szerveren, nem tudott belépni mert kikúrta.)
    */
    
    
    $allowedAddresses = array('127.0.0.1', '193.28.86.95', '195.228.45.25'); // Az alapból megadott telefonszámok a netfizetéstől érkező IP címek (Ezért azért kell hogy random ipkről ne lehessen ppt addolni.)
    $neededParameters = array("tel", "value", "prefix", "text");

    if (!in_array($_SERVER['REMOTE_ADDR'], $allowedAddresses))
        die('Nem szabad.. ' . $_SERVER['REMOTE_ADDR']);

    function paramsValidate($params_needed) {
         foreach ($params_needed as $param) {
            if (!isset($_GET[$param])) {
               die("Hianyzik a(z) " . $param . " parameter a keresbol.");
               return false;
            }
         }
         return true;
    }
    if (paramsValidate($neededParameters)) { // Ha minden get request megvan
        $phone = $_GET['tel'];
        $value = $_GET['value'];
        $prefix = $_GET['prefix'];
        $msg = $_GET['text'];

        $mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbTable);
        if (!$mysqli) {
            echo $errorMsg . ' #DB1';
            exit;
        }

        $query = $mysqli->query("INSERT INTO edsms VALUES ('', '".$phone."', '".$value."', '".$prefix."', '".$msg."')");
        if (!$query) {
            echo $errorMsg . ' #DB2';
            exit;
        }

        require('./mtasdk.php');
        $mta = new mta($serverip, $serverport, $username, $password);
        $resource = $mta->getResource($resourceName);

        if (!$resource) {
            echo $errorMsg . ' #GAME1';
            exit;
        }

        $resource->call($functionName, $phone, $value, $prefix, $msg);
        echo $successfulMsg;
    }
