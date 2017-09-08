(function() {
  'use strict';
  angular
    .module('minotaur')
    .controller('AlimentoController', AlimentoController)
    .service('AlimentoServices', AlimentoServices);
  /** @ngInject */
  function AlimentoController($scope,$uibModal,$location,uiGridConstants,alertify,toastr,AlimentoServices,GrupoAlimentoServices) {

    var vm = this;
    var params = $location.search();
    // GRILLA PRINCIPAL
      var paginationOptions = {
        pageNumber: 1,
        firstRow: 0,
        pageSize: 10,
        sort: uiGridConstants.DESC,
        sortName: null,
        search: null
      };
      vm.mySelectionGrid = [];
      vm.gridOptions = {
        paginationPageSizes: [10, 50, 100, 500, 1000],
        paginationPageSize: 10,
        enableFiltering: true,
        enableSorting: true,
        useExternalPagination: true,
        useExternalSorting: true,
        useExternalFiltering : true,
        enableRowSelection: true,
        enableRowHeaderSelection: true,
        enableFullRowSelection: false,
        multiSelect: false,
        appScopeProvider: vm
      }
      vm.gridOptions.columnDefs = [ 
        { field: 'idalimento', name:'idalimento', displayName: 'ID', minWidth: 50, sort: { direction: uiGridConstants.DESC} },
        { field: 'grupo1', name:'descripcion_gr1', displayName: 'GRUPO 1', minWidth: 160 },
        { field: 'grupo2', name:'descripcion_gr2', displayName: 'GRUPO 2', minWidth: 160 },
        { field: 'nombre', name:'nombre', displayName: 'ALIMENTO', minWidth: 300 },
        { field: 'calorias', name:'calorias', displayName: 'CALORÍAS', minWidth: 80 },
        { field: 'proteinas', name:'proteinas', displayName: 'PROTEÍNAS', minWidth: 80 },
        { field: 'grasas', name:'grasas', displayName: 'GRASAS', minWidth: 80 },
        { field: 'carbohidratos', name:'carbohidratos', displayName: 'CARBOHIDRATOS', minWidth: 80 },
        { field: 'accion', name:'accion', displayName: 'ACCION', minWidth: 80, enableFiltering: false,
          cellTemplate: '<div class="text-center">' +  
          '<button class="btn btn-default btn-sm text-green btn-action" ng-click="grid.appScope.btnEditar(row)"> <i class="fa fa-edit"></i> </button>'+
          '<button class="btn btn-default btn-sm text-red btn-action" ng-click="grid.appScope.btnAnular(row)"> <i class="fa fa-trash"></i> </button>' + 
          '</div>' 
        } 
      ];
      vm.gridOptions.onRegisterApi = function(gridApi) {
        vm.gridApi = gridApi;
        gridApi.selection.on.rowSelectionChanged($scope,function(row){
          vm.mySelectionGrid = gridApi.selection.getSelectedRows();
        });
        gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
          vm.mySelectionGrid = gridApi.selection.getSelectedRows();
        });
        gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) { 
          paginationOptions.pageNumber = newPage;
          paginationOptions.pageSize = pageSize;
          paginationOptions.firstRow = (paginationOptions.pageNumber - 1) * paginationOptions.pageSize;
          vm.getPaginationServerSide();
        });
        vm.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) {
          var grid = this.grid;
          paginationOptions.search = true;
          paginationOptions.searchColumn = { 
            'al.idalimento' : grid.columns[1].filters[0].term,
            'gr1.descripcion_gr1' : grid.columns[2].filters[0].term,
            'gr2.descripcion_gr2' : grid.columns[3].filters[0].term,
            'al.nombre' : grid.columns[4].filters[0].term,
            'al.calorias' : grid.columns[5].filters[0].term,
            'al.proteinas' : grid.columns[6].filters[0].term,
            'al.grasas' : grid.columns[7].filters[0].term,
            'al.carbohidratos' : grid.columns[8].filters[0].term 
          }
          // console.log('columnas',paginationOptions.searchColumn);
          vm.getPaginationServerSide();
        });
      }

      paginationOptions.sortName = vm.gridOptions.columnDefs[0].name;
      vm.getPaginationServerSide = function() { 
        vm.datosGrid = {
          paginate : paginationOptions
        };
        AlimentoServices.sListarAlimentos(vm.datosGrid).then(function (rpta) {
          vm.gridOptions.data = rpta.datos;
          vm.gridOptions.totalItems = rpta.paginate.totalRows;
          vm.mySelectionGrid = [];
        });
      }
      vm.getPaginationServerSide();
    // MANTENIMIENTO
      vm.btnNuevo = function () {
        var modalInstance = $uibModal.open({
          templateUrl: 'app/pages/alimento/alimento_formview.html',
          controllerAs: 'modalAli',
          size: 'lg',
          backdropClass: 'splash splash-2 splash-ef-14',
          windowClass: 'splash splash-2 splash-ef-14',
          // controller: 'ModalInstanceController',
          controller: function($scope, $uibModalInstance, arrToModal ){
            var vm = this;
            vm.fData = {};
            vm.modoEdicion = false;
            vm.getPaginationServerSide = arrToModal.getPaginationServerSide;
            vm.modalTitle = 'Registro de alimentos';
            vm.activeStep = 0;
            // GRUPO ALIMENTO 1
            GrupoAlimentoServices.sListarGrupoAlimento1().then(function (rpta) {
              vm.listaGrupo1 = angular.copy(rpta.datos);
              vm.listaGrupo1.splice(0,0,{ id : 0, descripcion:'--Seleccione una opción--'});
              vm.fData.idgrupo1 = vm.listaGrupo1[0];
            });    

            vm.cambiogrupo = function(){
              GrupoAlimentoServices.sListarGrupoAlimento2(vm.fData.idgrupo1.id).then(function (rpta) {
                vm.listaGrupo2 = angular.copy(rpta.datos);
                vm.listaGrupo2.splice(0,0,{ id : 0, descripcion:'--Seleccione una opción--'});
                vm.fData.idgrupo2 = vm.listaGrupo2[0];
              });              
            }        

            vm.aceptar = function () {
              AlimentoServices.sRegistrarAlimento(vm.fData).then(function (rpta) {
                var openedToasts = [];
                vm.options = {
                  timeout: '3000',
                  extendedTimeout: '1000',
                  preventDuplicates: false,
                  preventOpenDuplicates: false
                };
                if(rpta.flag == 1){
                  $uibModalInstance.close(vm.fData);                  
                  vm.getPaginationServerSide();
                  var title = 'OK';
                  var iconClass = 'success';
                }else if( rpta.flag == 0 ){
                  var title = 'Advertencia';
                  var iconClass = 'warning';
                }else{
                  alert('Ocurrió un error');
                }
                var toast = toastr[iconClass](rpta.message, title, vm.options);
                openedToasts.push(toast);
              });

            };
            vm.cancel = function () {
              $uibModalInstance.dismiss('cancel');
            };
          },
          resolve: {
            arrToModal: function() {
              return {
                getPaginationServerSide : vm.getPaginationServerSide,
              }
            }
          }
        });
      }
      vm.btnEditar = function(row){
        var modalInstance = $uibModal.open({
          templateUrl: 'app/pages/alimento/alimento_formview.html',
          controllerAs: 'modalAli',
          size: 'lg',
          backdropClass: 'splash splash-2 splash-ef-14',
          windowClass: 'splash splash-2 splash-ef-14',
          controller: function($scope, $uibModalInstance, arrToModal ){
            var vm = this;
            var openedToasts = [];
            vm.fData = {};
            vm.fData = angular.copy(arrToModal.seleccion);
            vm.modoEdicion = true;
            vm.getPaginationServerSide = arrToModal.getPaginationServerSide;
            vm.modalTitle = 'Edición de Alimentos';
            vm.activeStep = 0;

            GrupoAlimentoServices.sListarGrupoAlimento1().then(function (rpta) {
              vm.listaGrupo1 = {};
              vm.listaGrupo1 = angular.copy(rpta.datos);
              vm.listaGrupo1.splice(0,0,{ id : 0, descripcion:'--Seleccione una opción--'});
              angular.forEach(vm.listaGrupo1, function(value, key){
                if(value.id == vm.fData.idgrupo1){
                  vm.fData.idgrupo1 = vm.listaGrupo1[key];
                  GrupoAlimentoServices.sListarGrupoAlimento2(value.id).then(function (rpta) {
                    vm.listaGrupo2 = {};
                    vm.listaGrupo2 = angular.copy(rpta.datos);
                    vm.listaGrupo2.splice(0,0,{ id : 0, descripcion:'--Seleccione una opción--'});
                    angular.forEach(vm.listaGrupo2, function(value2, key2){
                      if(value2.id == vm.fData.idgrupo2){
                        vm.fData.idgrupo2 = vm.listaGrupo2[key2];
                      }                      
                    });
                  });                  
                }
              });
            });              

            vm.cambiogrupo = function(){
              GrupoAlimentoServices.sListarGrupoAlimento2(vm.fData.idgrupo1.id).then(function (rpta) {
                vm.listaGrupo2 = angular.copy(rpta.datos);
                vm.listaGrupo2.splice(0,0,{ id : 0, descripcion:'--Seleccione una opción--'});
                vm.fData.idgrupo2 = vm.listaGrupo2[0];
              });              
            }

            vm.aceptar = function () {
              $uibModalInstance.close(vm.fData);
              AlimentoServices.sEditarAlimento(vm.fData).then(function (rpta) {
                vm.options = {
                  timeout: '3000',
                  extendedTimeout: '1000',
                  progressBar: true,
                  preventDuplicates: false,
                  preventOpenDuplicates: false
                };
                if(rpta.flag == 1){
                  //$uibModalInstance.close(vm.fData); 
                  $uibModalInstance.dismiss('cancel');                  
                  vm.getPaginationServerSide();
                  var title = 'OK';
                  var iconClass = 'success';
                }else if( rpta.flag == 0 ){
                  var title = 'Advertencia';
                  var iconClass = 'warning';
                }else{
                  alert('Ocurrió un error');
                }
                var toast = toastr[iconClass](rpta.message, title, vm.options);
                openedToasts.push(toast);
              });

            };
            vm.cancel = function () {
              $uibModalInstance.dismiss('cancel');
            };
          },
          resolve: {
            arrToModal: function() {
              return {
                getPaginationServerSide : vm.getPaginationServerSide,
                seleccion : row.entity
              }
            }
          }
        });
      }
      vm.btnAnular = function(row){
        alertify.confirm("¿Realmente desea realizar la acción?", function (ev) {
          ev.preventDefault();
          AlimentoServices.sAnularAlimento(row.entity).then(function (rpta) {
            var openedToasts = [];
            vm.options = {
              timeout: '3000',
              extendedTimeout: '1000',
              preventDuplicates: false,
              preventOpenDuplicates: false
            };
            if(rpta.flag == 1){
              vm.getPaginationServerSide();
              var title = 'OK';
              var iconClass = 'success';
            }else if( rpta.flag == 0 ){
              var title = 'Advertencia';
              var iconClass = 'warning';
            }else{
              alert('Ocurrió un error');
            }
            var toast = toastr[iconClass](rpta.message, title, vm.options);
            openedToasts.push(toast);
          });
        }, function(ev) {
            ev.preventDefault();
        }); 
      }
      if(params.param == 'nuevo-alimento'){
        vm.btnNuevo();
        //$location.search({param: searchParam});
      }
  }

  function AlimentoServices($http, $q, handle) {
    return({
        sListarAlimentos: sListarAlimentos,
        sListaAlimentosAutocomplete: sListaAlimentosAutocomplete,
        sRegistrarAlimento: sRegistrarAlimento,
        sEditarAlimento: sEditarAlimento,
        sAnularAlimento: sAnularAlimento,
    });
    function sListarAlimentos(pDatos) {
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url :  angular.patchURLCI + "Alimentos/listar_alimentos",
            data : datos
      });
      return (request.then( handle.success,handle.error ));
    }
    function sListaAlimentosAutocomplete(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Alimentos/lista_alimentos_autocomplete",
            data : datos
      });
      return (request.then(handle.success,handle.error));
    }
    function sRegistrarAlimento(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Alimentos/registrar_alimento",
            data : datos
      });
      return (request.then(handle.success,handle.error));
    }
    function sEditarAlimento(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Alimentos/editar_alimento",
            data : datos
      });
      return (request.then(handle.success,handle.error));
    }
    function sAnularAlimento(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Alimentos/anular_alimento",
            data : datos
      });
      return (request.then(handle.success,handle.error));
    }
  }
  //
})();