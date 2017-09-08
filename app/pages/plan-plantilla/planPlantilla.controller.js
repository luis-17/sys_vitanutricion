(function() {
  'use strict';
  angular
    .module('minotaur')
    // .controller('PlanPlantillaController', PlanPlantillaController)
    .service('PlanPlantillaServices', PlanPlantillaServices);

  function PlanPlantillaServices($http, $q, handle) {
    return({ 
      sListarPlantillasCbo:sListarPlantillasCbo,
      sRegistrarPlanPlantilla:sRegistrarPlanPlantilla,
      sListarPlanPlantilla:sListarPlanPlantilla
      //sActualizarPlan:sActualizarPlan 
    });
    function sListarPlantillasCbo(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"PlanPlantilla/listar_plan_plantilla_cbo",
            data : datos
      });
      return (request.then(handle.success,handle.error));
    }
    function sRegistrarPlanPlantilla(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"PlanPlantilla/registrar_plan_plantilla",
            data : datos
      });
      return (request.then(handle.success,handle.error));
    }    
    function sListarPlanPlantilla(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"PlanPlantilla/listar_plan_plantilla",
            data : datos
      });
      return (request.then(handle.success,handle.error));
    } 
    // function sActualizarPlan(datos) {
    //   var request = $http({
    //         method : "post",
    //         url : angular.patchURLCI+"PlanPlantilla/actualizar_plan_plantilla",
    //         data : datos
    //   });
    //   return (request.then(handle.success,handle.error));
    // }
  }
})();