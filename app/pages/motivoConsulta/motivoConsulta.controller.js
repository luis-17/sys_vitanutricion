(function() {
  'use strict';
  angular
    .module('minotaur')
    .controller('MotivoConsultaController', MotivoConsultaController)
    .service('MotivoConsultaServices', MotivoConsultaServices);

  /** @ngInject */
  function MotivoConsultaController($scope,MotivoConsultaServices) {
    var vm = this;
  }
  function MotivoConsultaServices($http, $q, handle) {
    return({
        sListarMotivoConsultaCbo: sListarMotivoConsultaCbo
    });
    function sListarMotivoConsultaCbo(pDatos) {
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url :  angular.patchURLCI + "MotivoConsulta/listar_motivo_consulta_cbo",
            data : datos
      });
      return (request.then( handle.success,handle.error ));
    }
  }
})();
