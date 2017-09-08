(function() {
  'use strict';
  angular
    .module('minotaur')
    .controller('InformeGeneralController', InformeGeneralController)
    .service('InformeGeneralServices', InformeGeneralServices);

  /** @ngInject */
  function InformeGeneralController($scope) { 
    var vm = this;
  }
  function InformeGeneralServices($http, $q, handle) {
    return({
        sListarInformeGeneral: sListarInformeGeneral
    });
    function sListarInformeGeneral(pDatos) {
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url :  angular.patchURLCI + "InformeGeneral/listar_informe_general",
            data : datos
      });
      return (request.then( handle.success,handle.error ));
    }            
  }
})();