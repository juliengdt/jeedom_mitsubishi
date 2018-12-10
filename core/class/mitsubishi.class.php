<?php

/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class mitsubishi extends eqLogic {


  public function SetModif($option,$flag,$idflag){
    log::add('mitsubishi', 'info', 'Modification '.$flag.' '.$idflag.' '.$option);

    $montoken = config::byKey('MyToken', 'mitsubishi', '');
    if ($montoken != '') {

      $devideid = $this->getConfiguration('deviceid');
      $buildid = $this->getConfiguration('buildid');
      $typepac = $this->getConfiguration('typepac');

      $request = new com_http('https://app.melcloud.com/Mitsubishi.Wifi.Client/Device/Get?id=' . $devideid . '&buildingID=' . $buildid);
      $request->setHeader(array('X-MitsContextKey: ' . $montoken));
      $json = $request->exec(30000, 2);
      $device = json_decode($json, true);
      $device[$flag] = $option;
      $device['EffectiveFlags'] = $idflag;
      $device['HasPendingCommand'] = 'true';


      $ch = curl_init();

      if ($typepac == 'air/eau'){
        curl_setopt($ch, CURLOPT_URL, "https://app.melcloud.com/Mitsubishi.Wifi.Client/Device/SetAtw");
      }else{
        curl_setopt($ch, CURLOPT_URL, "https://app.melcloud.com/Mitsubishi.Wifi.Client/Device/SetAta");
      }

      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'X-MitsContextKey: ' . $montoken,
        'content-type: application/json'
      ));
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($device));
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $server_output = curl_exec($ch);
      curl_close($ch);
      $json = json_decode($server_output, true);
      foreach ($this->getCmd() as $cmd) {
        if ('NextCommunication' == $cmd->getName()) {
          $cmd->setCollectDate('');
          $time = strtotime($json['NextCommunication'] . " + 1 hours"); // Add 1 hour
          $time = date('G:i:s', $time); // Back to string
          $cmd->event($time);
        }
      }
    }
  }


  public static function gettoken() {
    $myemail = config::byKey('MyEmail', 'mitsubishi');
    $monpass = config::byKey('MyPassword', 'mitsubishi');

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://app.melcloud.com/Mitsubishi.Wifi.Client/Login/ClientLogin");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS,
    "Email=" . $myemail . "&Password=" . $monpass . "&Language=7&AppVersion=1.10.1.0&Persist=true&CaptchaChallenge=null&CaptchaChallenge=null");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $server_output = curl_exec($ch);
    curl_close($ch);

    $json = json_decode($server_output, true);

    if ($json['ErrorId'] == null) {
      log::add('mitsubishi', 'debug', 'Login ok ');
      config::save("MyToken", $json['LoginData']['ContextKey'], 'mitsubishi');
    } else {
      log::add('mitsubishi', 'debug', 'Login ou mot de passe Melcloud incorrecte.');
      config::save("MyToken", $json['ErrorId'], 'mitsubishi');
    }

  }

  public static function cron5() {
    mitsubishi::refresh();
  }

  public static function refresh() {
    $montoken = config::byKey('MyToken', 'mitsubishi', '');
    if ($montoken == '') {
      mitsubishi::gettoken();
    }
    if ($montoken != '') {
      log::add('mitsubishi', 'info', 'pull 5 minutes mytoken =' . $montoken);
      //$request = new com_http('https://app.melcloud.com/Mitsubishi.Wifi.Client/Device/Get?id='.$devideid.'&buildingID='.$buildid);
      $request = new com_http('https://app.melcloud.com/Mitsubishi.Wifi.Client/User/ListDevices');
      $request->setHeader(array('X-MitsContextKey: ' . $montoken));
      $json = $request->exec(30000, 2);

      $values = json_decode($json, true);
      foreach ($values as $maison) {
        log::add('mitsubishi', 'debug', 'Maison ' . $maison['Name']);
        for ($i = 0; $i < count($maison['Structure']['Devices']); $i++) {
          log::add('mitsubishi', 'debug', 'pull : device 1 ' . $i . ' ' . $device['DeviceName']);
          $device = $maison['Structure']['Devices'][$i];
          self::pullCommande($device);
        }
        // FLOORS
        for ($a = 0; $a < count($maison['Structure']['Floors']); $a++) {
          log::add('mitsubishi', 'debug', 'FLOORS ' . $a);
          // AREAS IN FLOORS
          for ($i = 0; $i < count($maison['Structure']['Floors'][$a]['Areas']); $i++) {
            for ($d = 0; $d < count($maison['Structure']['Floors'][$a]['Areas'][$i]['Devices']); $d++) {
              $device = $maison['Structure']['Floors'][$a]['Areas'][$i]['Devices'][$d];
              self::pullCommande($device);
            }
          }
          // FLOORS
          for ($i = 0; $i < count($maison['Structure']['Floors'][$a]['Devices']); $i++) {
            $device = $maison['Structure']['Floors'][$a]['Devices'][$i];
            self::pullCommande($device);
          }
        }
        // AREAS
        for ($a = 0; $a < count($maison['Structure']['Areas']); $a++) {
          log::add('mitsubishi', 'info', 'AREAS ' . $a);
          for ($i = 0; $i < count($maison['Structure']['Areas'][$a]['Devices']); $i++) {
            log::add('mitsubishi', 'info', 'machine AREAS ' . $i);
            $device = $maison['Structure']['Areas'][$a]['Devices'][$i];
            self::pullCommande($device);
          }
        }
      }
    }
  }

  public function loadCmdFromConf($type) {
		if (!is_file(dirname(__FILE__) . '/../config/devices/' . $type . '.json')) {
			return;
		}
		$content = file_get_contents(dirname(__FILE__) . '/../config/devices/' . $type . '.json');
		if (!is_json($content)) {
			return;
		}
		$device = json_decode($content, true);
		if (!is_array($device) || !isset($device['commands'])) {
			return true;
		}
		foreach ($device['commands'] as $command) {
			$cmd = null;
			foreach ($this->getCmd() as $liste_cmd) {
				if ((isset($command['logicalId']) && $liste_cmd->getLogicalId() == $command['logicalId'])
				|| (isset($command['name']) && $liste_cmd->getName() == $command['name'])) {
					$cmd = $liste_cmd;
					break;
				}
			}
			if ($cmd == null || !is_object($cmd)) {
				$cmd = new shellyCmd();
				$cmd->setEqLogic_id($this->getId());
				utils::a2o($cmd, $command);
				$cmd->save();
			}
		}
	}

  private static function pullCommande($device) {

    log::add('mitsubishi', 'debug', 'pull : ' . $device['DeviceName']);
    if ($device['DeviceID'] == '') return;
    log::add('mitsubishi', 'debug', $device['DeviceID'] . ' ' . $device['DeviceName']);

    $theEQlogic = eqLogic::byTypeAndSearhConfiguration('mitsubishi','"namemachine":"'.$device['DeviceName'].'"');
    if (count($theEQlogic) == 0){

      $mylogical = new mitsubishi();
      $mylogical->setIsVisible(0);
      $mylogical->setIsEnable(0);
      $mylogical->setEqType_name('mitsubishi');
      $mylogical->setName($device['DeviceName']);
      $mylogical->setConfiguration('namemachine',$device['DeviceName']);
      $mylogical->save();

    }else{

      $mylogical =  $theEQlogic[0];

      if ($mylogical->getIsEnable()) {

        log::add('mitsubishi', 'debug', 'setdevice ' . $device['Device']['DeviceID']);
        $mylogical->setConfiguration('deviceid', $device['Device']['DeviceID']);
        $mylogical->setConfiguration('buildid', $device['BuildingID']);

        if ($device['Device']['DeviceType'] == '0'){
          $mylogical->setConfiguration('typepac', 'air/air');
          $mylogical->loadCmdFromConf('air');
        }
        if ($device['Device']['DeviceType'] == '1'){
          $mylogical->setConfiguration('typepac', 'air/eau');
          $mylogical->loadCmdFromConf('water');
        }

        $device['Device']['ListHistory24Formatters'] = '';


        if ($mylogical->getConfiguration('rubriques') == '' )
        {
          $mylogical->setConfiguration('rubriques', print_r($device['Device'],true));
        }

        $mylogical->save();

        foreach ($mylogical->getCmd() as $cmd) {

          switch ($cmd->getLogicalId()) {

            case 'OperationMode':
            $cmd->setCollectDate('');

            switch ($device['Device'][$cmd->getLogicalId()]){
              case 7:
              $cmd->event('Ventilation');
              break;
              case 1:
              $cmd->event('Chauffage');
              break;
              case 2:
              $cmd->event('Sechage');
              break;
              case 3:
              $cmd->event('Froid');
              break;
              case 8:
              $cmd->event('Automatique');
              break;
            }

            break;


            case 'Rafraichir':
            log::add('mitsubishi', 'debug', 'log ' . $cmd->getLogicalId() . ' .On ne traite pas cette commande');
            break;
            default:

            if ($cmd->getType() == 'action'){

              log::add('mitsubishi', 'debug', 'log action '.$cmd->getName().' ' . $cmd->getLogicalId() . ' ' . $device['Device'][$cmd->getLogicalId()]);
              $cmd->setConfiguration('lastCmdValue',$device['Device'][$cmd->getLogicalId()]);

            }else{

              log::add('mitsubishi', 'debug', 'log info '.$cmd->getName().' ' . $cmd->getLogicalId() . ' ' . $device['Device'][$cmd->getLogicalId()]);
              if ('LastTimeStamp' == $cmd->getLogicalId()) {
                $cmd->event(str_replace('T', ' ', $device['Device'][$cmd->getLogicalId()]));
              } else {
                $cmd->setCollectDate('');
                $cmd->event($device['Device'][$cmd->getLogicalId()]);
              }

            }

            $cmd->save();

            break;
          }

        }
      }
    }
  }

}

class mitsubishiCmd extends cmd {
  public function execute($_options = array()) {
    if ($this->getType() == 'action') {
      $Eqlogic = $this->getEqLogic();
      if ($this->getSubType() == 'slider') {
        $option = $_options['slider'];
      } else {
        $option = $this->getConfiguration('option');
      }
      $Eqlogic->SetModif($option,$this->getConfiguration('flag'),$this->getConfiguration('idflag'));
      mitsubishi::refresh();
    }
  }

}

?>
