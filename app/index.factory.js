(function() {
  'use strict';

  angular
    .module('minotaur')
    .factory('CalendarData', CalendarData)
    .factory('ModalReporteFactory', ModalReporteFactory);

  /** @ngInject */

  /* Esta factoria ya no la uso, no lo tomen en cuenta para el Calendar por ahora. */
  function CalendarData($http/*,rootServices*/) { 
    var interfazCalendarData = {};
    interfazCalendarData.getDataCalendarCitas = getDataCalendarCitas;
    return interfazCalendarData;

    function getDataCalendarCitas(arrParams) {
      return $http.post(arrParams.url, arrParams.datos).then(handleSuccess, handleError('Recurso no encontrado'));
    }
  }
  function ModalReporteFactory($uibModal,$http,$q,pageLoading,pinesNotifications,rootServices,PlanAlimentarioServices,ConsultasServices,PacienteServices){
    var interfazReporte = {
      getPopupReporte: function(arrParams){ //console.log(arrParams.datos.salida,' as');
        if( arrParams.datos.salida == 'pdf' || angular.isUndefined(arrParams.datos.salida) ){
          $uibModal.open({
            templateUrl: 'app/pages/reportes/popup_reporte.php',
            controllerAs: 'mr',
            size: 'lg',
            controller: function ($scope,$uibModalInstance,arrParams) {
              $scope.titleModalReporte = arrParams.titulo;
              $scope.envioCorreoEnabled = false;
              if( arrParams.envio_correo == 'si' ){
                $scope.envioCorreoEnabled = true;
              }
              $scope.cancel = function () {
                $uibModalInstance.dismiss('cancel');
              }
              $scope.enviarCorreo = function() { 
                $uibModal.open({
                  templateUrl: 'app/pages/reportes/popup_envio_correo.php',
                  controllerAs: 'ec',
                  size: 'lg',
                  controller: function ($scope,$uibModalInstance) { 
                    $scope.titleModalReporteEmail = 'Envío de Correo'; 
                    $scope.fEnvio = {}; 
                    // OBTENER CORREO DE CLIENTE 
                    var arrParamsCliente = {
                      'idcliente': arrParams.datos.consulta.idcliente
                    };
                    PacienteServices.sListarPacientePorId(arrParamsCliente).then(function(rpta) { 
                      if(rpta.flag == 1){ 
                        $scope.fEnvio.emails = rpta.datos.email;
                        // console.log('ver ficha');
                        // vm.mySelectionGrid[0] = angular.copy(rpta.datos);
                        // vm.btnVerFicha(rpta.datos);
                      }
                    });
                    
                    
                    $scope.envioCorreoExec = function() {
                      if(arrParams.titulo == 'CONSULTA'){ 
                        pageLoading.start('Enviando Ficha de Paciente...'); 
                        var datos = { 
                          consulta: arrParams.datos.consulta,
                          salida: 'correo',
                          emails: $scope.fEnvio.emails
                        }; 
                        ConsultasServices.sGenerarPDFConsulta(datos).then(function(rpta){
                          if(rpta.flag == 1){      
                            var pTitle = 'OK!';
                            var pType = 'success';
                          }else if( rpta.flag == 0 ){
                            var pTitle = 'Advertencia!';
                            var pType = 'warning';  
                          }
                          $uibModalInstance.dismiss('cancel'); 
                          pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 }); 
                          pageLoading.stop();
                        });
                      }
                      if(arrParams.titulo == 'PLAN ALIMENTARIO'){
                        pageLoading.start('Enviando Plan Alimentario...'); 
                        var datos = {
                          cita: arrParams.datos.cita,
                          consulta: arrParams.datos.consulta,
                          salida: 'correo',
                          emails: $scope.fEnvio.emails
                        }
                        PlanAlimentarioServices.sGenerarPdfPlan(datos).then(function(rpta){
                          if(rpta.flag == 1){      
                            var pTitle = 'OK!';
                            var pType = 'success';
                          }else if( rpta.flag == 0 ){
                            var pTitle = 'Advertencia!';
                            var pType = 'warning';  
                          }
                          $uibModalInstance.dismiss('cancel'); 
                          pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 }); 
                          pageLoading.stop();
                        });
                      }
                    }
                    $scope.detCancel = function () { 
                      $uibModalInstance.dismiss('cancel');
                    }
                  } 
                });
              }
              var deferred = $q.defer();
              $http.post(arrParams.url, arrParams.datos).then(
                function(res) { 
                    $('#frameReporte').attr("src", res.data.urlTempPDF); 
                    deferred.resolve(res.data);
                },
                function(err) { 
                    deferred.resolve(err);
                }
              );
            },
            resolve: {
              arrParams: function() {
                return arrParams;
              }
            }
          });
        }
      }

    }
    return interfazReporte;
  }
})();
