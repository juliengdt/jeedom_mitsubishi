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

function initmitsubishiPanel() {
    displaymitsubishi();
    $(window).on("orientationchange", function (event) {
        setTileSize('.eqLogic');
        $('#div_displayEquipement').packery({gutter : 4});
    });
}

function displaymitsubishi() {
    $.showLoading();
    $.ajax({
        type: 'POST',
        url: 'plugins/mitsubishi/core/ajax/mitsubishi.ajax.php',
        data: {
            action: 'getMitsubishi',
            version: 'mview'
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) {
            if (data.state != 'ok') {
                $('#div_alert').showAlert({message: data.result, level: 'danger'});
                return;
            }
            if(data.result.length == 0){
                $('#div_location').hide();
            }else{
                $('#div_location').show();
                $('#div_displayEquipementmitsubishi').empty();
                for (var i in data.result) {
                    $('#div_displayEquipementmitsubishi').append(data.result[i]).trigger('create');
                }
            }
            setTileSize('.eqLogic');
            $('#div_displayEquipementmitsubishi').packery({gutter : 4});
            $.hideLoading();
        }
    });
}
