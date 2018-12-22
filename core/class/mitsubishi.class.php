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

  public static function cron5() {
    if (config::byKey('token', 'mitsubishi', '') == '') {
      mitsubishi::getToken();
    }
    mitsubishi::refreshAll();
  }

  public static function refreshAll() {
    $json = mitsubishi::callMelcloud('https://app.melcloud.com/mitsubishi.Wifi.Client/User/ListDevices',array('X-MitsContextKey: ' . config::byKey('token', 'mitsubishi')),array());
    //log::add('mitsubishi', 'debug', 'Retrieve ' . print_r($json, true));
    log::add('mitsubishi', 'debug', 'Retrieve ' . config::byKey('token', 'mitsubishi'));
    foreach ($json as $building) {
      log::add('mitsubishi', 'debug', 'Building ' . $building['ID']);
      foreach ($json as $building) {
        foreach ($building['Structure']['Devices'] as $device) {
          log::add('mitsubishi', 'debug', 'Retrieve ' . print_r($device, true));
          $mitsubishi=mitsubishi::byLogicalId($device['BuildingID'] . $device['DeviceID'], 'mitsubishi');
          if (!is_object($mitsubishi)) {
            $mitsubishi = new mitsubishi();
            $mitsubishi->setEqType_name('mitsubishi');
            $mitsubishi->setLogicalId($device['BuildingID'] . $device['DeviceID']);
            $mitsubishi->setIsEnable(1);
            $mitsubishi->setIsVisible(1);
            $mitsubishi->setName($device['DeviceName']);
            $mitsubishi->setConfiguration('DeviceID', $device['DeviceID']);
            $mitsubishi->setConfiguration('BuildingID', $device['BuildingID']);
            $mitsubishi->setConfiguration('DeviceType', $device['Device']['DeviceType']);//0 air/air, 1 air/water
            $mitsubishi->save();
          }
          if ($device['Device']['DeviceType'] == 0) {
            $mitsubishi->loadCmdFromConf('air');
            $mitsubishi->checkAndUpdateCmd('ActualFanSpeed', $device['Device']['ActualFanSpeed']);
            $mitsubishi->checkAndUpdateCmd('RoomTemperature', $device['Device']['RoomTemperature']);
            $mitsubishi->checkAndUpdateCmd('OperationMode', $device['Device']['OperationMode']);
          } else {
            $mitsubishi->loadCmdFromConf('water');
            $mitsubishi->checkAndUpdateCmd('RoomTemperatureZone1', $device['Device']['RoomTemperatureZone1']);
            $mitsubishi->checkAndUpdateCmd('RoomTemperatureZone2', $device['Device']['RoomTemperatureZone2']);
            $mitsubishi->checkAndUpdateCmd('OutdoorTemperature', $device['Device']['OutdoorTemperature']);
            $mitsubishi->checkAndUpdateCmd('TankWaterTemperature', $device['Device']['TankWaterTemperature']);
            $mitsubishi->checkAndUpdateCmd('ForcedHotWaterMode', $device['Device']['ForcedHotWaterMode']);
            $mitsubishi->checkAndUpdateCmd('EcoHotWater', $device['Device']['EcoHotWater']);
            $mitsubishi->checkAndUpdateCmd('Power', $device['Device']['Power']);
          }

        }
      }
    }
  }

  public static function getToken() {
    if (config::byKey('password', 'mitsubishi') != '' && config::byKey('mail', 'mitsubishi') != '') {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, "https://app.melcloud.com/Mitsubishi.Wifi.Client/Login/ClientLogin");
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS,
      "Email=" . config::byKey('mail', 'mitsubishi') . "&Password=" . config::byKey('password', 'mitsubishi') . "&Language=7&AppVersion=1.10.1.0&Persist=true&CaptchaChallenge=null&CaptchaChallenge=null");
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $server_output = curl_exec($ch);
      curl_close($ch);
      $json = json_decode($server_output, true);
      //$data = 'Email='. config::byKey('mail', 'mitsubishi') . '&Password=' . config::byKey('password', 'mitsubishi') . '&Language=7&AppVersion=1.7.1.0&Persist=true&CaptchaChallenge=null&CaptchaResponse=null';
      //$json = mitsubishi::callMelcloud('https://app.melcloud.com/mitsubishi.Wifi.Client/Login/ClientLogin',array(),$data);
      log::add('mitsubishi', 'debug', 'Retrieve ' . print_r($json, true));
      if ($json['ErrorId'] == null) {
        config::save("token", $json['LoginData']['ContextKey'], 'mitsubishi');
      } else {
        log::add('mitsubishi', 'debug', 'Connexion error');
      }
    }
  }

  public static function callMelcloud($_url = '', $_header = array(), $_data = array()) {
    $request_http = new com_http($_url);
    if (!empty($_data)) {
      $request_http->setPost($_data);
    }
    if (!empty($_header)) {
      $request_http->setHeader($_header);
    }
    $output = $request_http->exec(30);
    return json_decode($output, true);
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
  			$cmd = new geotravCmd();
  			$cmd->setEqLogic_id($this->getId());
  			utils::a2o($cmd, $command);
  			$cmd->save();
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
      mitsubishi::refreshAll();
    }
  }

}

?>
