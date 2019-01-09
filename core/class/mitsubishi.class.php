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
    $eqLogics = eqLogic::byType('mitsubishi', true);
    foreach ($eqLogics as $eqLogic) {
      if ($eqLogic->getConfiguration('SubType') == 'water') {
        $eqLogic->getConso();
        $eqLogic->getMode();
      }
    }
  }

  public static function cronDaily() {
    mitsubishi::getToken();
    $eqLogics = eqLogic::byType('mitsubishi', true);
    foreach ($eqLogics as $eqLogic) {
      if ($eqLogic->getConfiguration('SubType') == 'water') {
        $eqLogic->getConso();
        $eqLogic->getMode();
      }
    }
  }

  public static function refreshAll() {
    $json = mitsubishi::callMelcloud('https://app.melcloud.com/mitsubishi.Wifi.Client/User/ListDevices',array('X-MitsContextKey: ' . config::byKey('token', 'mitsubishi')),array());
    //log::add('mitsubishi', 'debug', 'Retrieve ' . print_r($json, true));
    foreach ($json as $building) {
      foreach ($json as $building) {
        foreach ($building['Structure']['Devices'] as $device) {
          log::add('mitsubishi', 'debug', 'Retrieve ' . print_r($device, true));
          if ($device['Device']['DeviceType'] == 0) {
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
              $mitsubishi->setConfiguration('SubType', 'air');
              $mitsubishi->save();
            }
            $mitsubishi->loadCmdFromConf('air');
            $mitsubishi->checkAndUpdateCmd('ActualFanSpeed', $device['Device']['ActualFanSpeed']);
            $mitsubishi->checkAndUpdateCmd('RoomTemperature', $device['Device']['RoomTemperature']);
            $mitsubishi->checkAndUpdateCmd('OperationMode', $device['Device']['OperationMode']);
            $mitsubishi->updateOperationMode($device['Device']['OperationMode']);
            $mitsubishi->checkAndUpdateCmd('Power', $device['Device']['Power']);
          } else {
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
              $mitsubishi->setConfiguration('SubType', 'water');
              $mitsubishi->save();
            }
            $mitsubishi->loadCmdFromConf('water');
            $mitsubishi->checkAndUpdateCmd('OutdoorTemperature', $device['Device']['OutdoorTemperature']);
            $mitsubishi->checkAndUpdateCmd('Power', $device['Device']['Power']);
            $mitsubishi->checkAndUpdateCmd('HolidayMode',$device['Device']['HolidayMode']);
            $mitsubishi->checkAndUpdateCmd('notOffline',!$device['Device']['Offline']);
            $mitsubishi=mitsubishi::byLogicalId($device['BuildingID'] . $device['DeviceID'] . 'ECS', 'mitsubishi');
            if (!is_object($mitsubishi)) {
              $mitsubishi = new mitsubishi();
              $mitsubishi->setEqType_name('mitsubishi');
              $mitsubishi->setLogicalId($device['BuildingID'] . $device['DeviceID'] . 'ECS');
              $mitsubishi->setIsEnable(1);
              $mitsubishi->setIsVisible(1);
              $mitsubishi->setName($device['DeviceName'] . 'ECS');
              $mitsubishi->setConfiguration('DeviceID', $device['DeviceID']);
              $mitsubishi->setConfiguration('BuildingID', $device['BuildingID']);
              $mitsubishi->setConfiguration('DeviceType', $device['Device']['DeviceType']);//0 air/air, 1 air/water
              $mitsubishi->setConfiguration('SubType', 'waterECS');
              $mitsubishi->save();
            }
            $mitsubishi->loadCmdFromConf('waterECS');
            $mitsubishi->checkAndUpdateCmd('TankWaterTemperature', $device['Device']['TankWaterTemperature']);
            $mitsubishi->checkAndUpdateCmd('SetTankWaterTemperature', $device['Device']['SetTankWaterTemperature']);
            $mitsubishi->checkAndUpdateCmd('ForcedHotWaterMode', $device['Device']['ForcedHotWaterMode']);
            $mitsubishi->checkAndUpdateCmd('EcoHotWater', $device['Device']['EcoHotWater']);
            $mitsubishi=mitsubishi::byLogicalId($device['BuildingID'] . $device['DeviceID'] . 'Zone1', 'mitsubishi');
            if (!is_object($mitsubishi)) {
              $mitsubishi = new mitsubishi();
              $mitsubishi->setEqType_name('mitsubishi');
              $mitsubishi->setLogicalId($device['BuildingID'] . $device['DeviceID'] . 'Zone1');
              $mitsubishi->setIsEnable(1);
              $mitsubishi->setIsVisible(1);
              $mitsubishi->setName($device['DeviceName'] . 'Zone1');
              $mitsubishi->setConfiguration('DeviceID', $device['DeviceID']);
              $mitsubishi->setConfiguration('BuildingID', $device['BuildingID']);
              $mitsubishi->setConfiguration('DeviceType', $device['Device']['DeviceType']);//0 air/air, 1 air/water
              $mitsubishi->setConfiguration('SubType', 'waterZone1');
              $mitsubishi->save();
            }
            $mitsubishi->loadCmdFromConf('waterZone1');
            $mitsubishi->checkAndUpdateCmd('RoomTemperatureZone1', $device['Device']['RoomTemperatureZone1']);
            $mitsubishi->checkAndUpdateCmd('SetTemperatureZone1', $device['Device']['SetTemperatureZone1']);
            $mitsubishi->checkAndUpdateCmd('OperationModeZone1', $device['Device']['OperationModeZone1']);
            $mitsubishi->updateOperationModeZone('OperationModeZone1',$device['Device']['OperationModeZone1']);
            $mitsubishi->checkAndUpdateCmd('IdleZone1',$device['Device']['IdleZone1']);
            $mitsubishi->checkAndUpdateCmd('SetHeatFlowTemperatureZone1',$device['Device']['SetHeatFlowTemperatureZone1']);
            $mitsubishi=mitsubishi::byLogicalId($device['BuildingID'] . $device['DeviceID'] . 'Zone2', 'mitsubishi');
            if (!is_object($mitsubishi)) {
              $mitsubishi = new mitsubishi();
              $mitsubishi->setEqType_name('mitsubishi');
              $mitsubishi->setLogicalId($device['BuildingID'] . $device['DeviceID'] . 'Zone2');
              $mitsubishi->setIsEnable(1);
              $mitsubishi->setIsVisible(1);
              $mitsubishi->setName($device['DeviceName'] . 'Zone2');
              $mitsubishi->setConfiguration('DeviceID', $device['DeviceID']);
              $mitsubishi->setConfiguration('BuildingID', $device['BuildingID']);
              $mitsubishi->setConfiguration('DeviceType', $device['Device']['DeviceType']);//0 air/air, 1 air/water
              $mitsubishi->setConfiguration('SubType', 'waterZone2');
              $mitsubishi->save();
            }
            $mitsubishi->loadCmdFromConf('waterZone2');
            $mitsubishi->checkAndUpdateCmd('RoomTemperatureZone2', $device['Device']['RoomTemperatureZone2']);
            $mitsubishi->checkAndUpdateCmd('SetTemperatureZone2', $device['Device']['SetTemperatureZone2']);
            $mitsubishi->checkAndUpdateCmd('OperationModeZone2', $device['Device']['OperationModeZone2']);
            $mitsubishi->updateOperationModeZone('OperationModeZone2',$device['Device']['OperationModeZone2']);
            $mitsubishi->checkAndUpdateCmd('IdleZone2',$device['Device']['IdleZone2']);
            $mitsubishi->checkAndUpdateCmd('SetHeatFlowTemperatureZone2',$device['Device']['SetHeatFlowTemperatureZone2']);
          }

        }
      }
    }
  }

  public function getConso() {
    $headers = array();
    $headers[] = 'Content-Type: application/json; charset=utf-8';
    $headers[] = 'X-Mitscontextkey: ' . config::byKey('token', 'mitsubishi');
    $post="{\"DeviceId\":" . $this->getConfiguration('DeviceID') . ",\"FromDate\":\"" . date('Y-m-d', strtotime("1 day ago" )) . "T00:00:00\",\"ToDate\":\"" . date('Y-m-d', strtotime("1 day ago" )) . "T00:00:00\",\"UseCurrency\":false}";
    $json = mitsubishi::callMelcloud('https://app.melcloud.com/Mitsubishi.Wifi.Client/EnergyCost/Report',$headers,$post);
    log::add('mitsubishi', 'debug', 'Retrieve ' . print_r($json, true));
    $this->checkAndUpdateCmd('HotWater', $json['HotWater'][0]);
    $this->checkAndUpdateCmd('Heating', $json['Heating'][0]);
    $this->checkAndUpdateCmd('ProducedHotWater', $json['ProducedHotWater'][0]);
    $this->checkAndUpdateCmd('ProducedHeating', $json['ProducedHeating'][0]);
    $this->checkAndUpdateCmd('CoP', $json['CoP'][0]);
  }

  public function getMode() {
    $headers = array();
    $headers[] = 'Content-Type: application/json; charset=utf-8';
    $headers[] = 'X-Mitscontextkey: ' . config::byKey('token', 'mitsubishi');
    $post="{\"DeviceId\":" . $this->getConfiguration('DeviceID') . ",\"FromDate\":\"" . date('Y-m-dTG:00:00', strtotime("1 day ago" )) . "\",\"ToDate\":\"" . date('Y-m-dTG:00:00') . "\",\"Duration\":1}";
    $json = mitsubishi::callMelcloud('https://app.melcloud.com/Mitsubishi.Wifi.Client/Report/GetOperationModeLog2',$headers,$post);
    log::add('mitsubishi', 'debug', 'Retrieve ' . $post);
    log::add('mitsubishi', 'debug', 'Retrieve ' . print_r($json, true));
    foreach ($json as $array) {
      $value = floatval($array['Value'])*100;
      switch ($array['Key']) {
        case 'Stop':
          $this->checkAndUpdateCmd('ModeStop', $value);
          break;
        case 'HotWater':
          $this->checkAndUpdateCmd('ModeHotWater', $value);
            break;
        case 'Heating':
          $this->checkAndUpdateCmd('ModeHeating', $value);
          break;
        case 'LegionellaPrevention':
          $this->checkAndUpdateCmd('ModeLegionellaPrevention', $value);
          break;
        case 'PowerOff':
          $this->checkAndUpdateCmd('ModePowerOff', $value);
          break;
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

  public function setModif($_option,$_flag,$_idflag){
    if (config::byKey('token', 'mitsubishi') != '') {
      $device = mitsubishi::callMelcloud('https://app.melcloud.com/Mitsubishi.Wifi.Client/Device/Get?id=' . $this->getConfiguration('DeviceID') . '&buildingID=' . $this->getConfiguration('BuildingID'),array('X-MitsContextKey: ' . config::byKey('token', 'mitsubishi')),array());
      $device[$_flag] = $_option;
      $device['EffectiveFlags'] = $_idflag;
      $device['HasPendingCommand'] = 'true';
      log::add('mitsubishi', 'debug', 'Set ' . print_r($device, true));
      if ($this->getConfiguration('DeviceType') == 1){
        $url = "https://app.melcloud.com/Mitsubishi.Wifi.Client/Device/SetAtw";
      }else{
        $url = "https://app.melcloud.com/Mitsubishi.Wifi.Client/Device/SetAta";
      }
      $headers = array(
        'X-MitsContextKey: ' . config::byKey('token', 'mitsubishi'),
        'content-type: application/json'
      );
      $post = json_encode($device);
      $json = mitsubishi::callMelcloud($url,$headers,$post);
      log::add('mitsubishi', 'debug', 'Result ' . print_r($json, true));
      //set op mode in text format
      if ($_flag == 'OperationMode'){
        $this->updateOperationMode($_option);
        $this->checkAndUpdateCmd('OperationMode', $_option);
      }
    }
  }

  public function updateOperationMode($_option){
    switch ($_option){
      case 7:
      $value = 'Ventilation';
      break;
      case 1:
      $value = 'Chauffage';
      break;
      case 2:
      $value = 'Sechage';
      break;
      case 3:
      $value = 'Froid';
      break;
      case 8:
      $value = 'Automatique';
      break;
    }
    $this->checkAndUpdateCmd('OperationModeText', $value);
  }
  public function updateOperationModeZone($_zone,$_option){
    switch ($_option){
      case 0:
      $value = 'Thermostat';
      break;
      case 1:
      $value = 'Température Eau';
      break;
      case 2:
      $value = 'Loi Eau';
      break;
    }
    $this->checkAndUpdateCmd($_zone . 'Text', $value);
  }


  public function loadCmdFromConf($_type) {
    if (!is_file(dirname(__FILE__) . '/../config/devices/' . $_type . '.json')) {
      return;
    }
    $content = file_get_contents(dirname(__FILE__) . '/../config/devices/' . $_type . '.json');
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
      log::add('mitsubishi', 'debug', 'Action ' . $option . ' ' . $this->getConfiguration('flag') . ' ' . $this->getConfiguration('idflag'));
      $Eqlogic->setModif($option,$this->getConfiguration('flag'),$this->getConfiguration('idflag'));
      mitsubishi::refreshAll();
    }
  }

}

?>
