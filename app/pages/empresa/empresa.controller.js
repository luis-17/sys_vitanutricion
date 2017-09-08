(function() {
  'use strict';

  angular
    .module('minotaur')
    .controller('EmpresaController', EmpresaController)
    .service('EmpresaServices', EmpresaServices);

  /** @ngInject */
  function EmpresaController($scope,$uibModal,$timeout,$location,filterFilter, uiGridConstants,$document, alertify,toastr,EmpresaServices) {

    var vm = this;
    var params = $location.search();
    vm.selectedItem = {};
    vm.options = {};
    vm.fDemo = {};

    vm.remove = function(scope) {
      scope.remove();
    };

    vm.toggle = function(scope) {
      scope.toggle();
    };

    vm.expandAll = function() {
      vm.$broadcast('angular-ui-tree:expand-all');
    };

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
        // showGridFooter: true,
        // showColumnFooter: true,
        enableRowSelection: true,
        enableRowHeaderSelection: false,
        enableFullRowSelection: true,
        multiSelect: false,
        appScopeProvider: vm
      }
      vm.gridOptions.columnDefs = [
        { field: 'idempresa', name:'idempresa', displayName: 'ID', width: 80, enableFiltering: false, },
        { field: 'nombre_comercial', name:'nombre_comercial', displayName: 'NOMBRE COMERCIAL', width: 200, },
        { field: 'razon_social', name:'razon_social', displayName: 'RAZON SOCIAL' },
        { field: 'ruc', name:'ruc', displayName: 'RUC', width: 150, },
        { field: 'celular', name:'celular', displayName: 'CELULAR', width: 150, },
        { field: 'personal_contacto', name:'personal_contacto', displayName: 'CONTACTO' },        
        { field: 'accion', name:'accion', displayName: 'ACCION', width: 80, enableFiltering: false,
          cellTemplate:'<label class="btn btn-sm text-primary" ng-click="grid.appScope.btnEditar(row);$event.stopPropagation();" tooltip-placement="left" uib-tooltip="EDITAR"> <i class="fa fa-edit"></i> </label>'+
          '<label class="btn btn-sm text-red" ng-click="grid.appScope.btnAnular(row);$event.stopPropagation();"> <i class="fa fa-trash" tooltip-placement="left" uib-tooltip="ELIMINAR!"></i> </label>'
         },

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
            'nombre_comercial' : grid.columns[2].filters[0].term,
            'razon_social' : grid.columns[3].filters[0].term,
            'ruc' : grid.columns[4].filters[0].term,
            'celular' : grid.columns[5].filters[0].term,
            'personal_contacto' : grid.columns[6].filters[0].term,          
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
        EmpresaServices.sListarEmpresas(vm.datosGrid).then(function (rpta) {
          vm.gridOptions.data = rpta.datos;
          vm.gridOptions.totalItems = rpta.paginate.totalRows;
          vm.mySelectionGrid = [];
        });
      }
      vm.getPaginationServerSide();
      /*---------- NUEvA EMPRESA--------*/
      vm.btnNuevo = function () {
        var modalInstance = $uibModal.open({
          templateUrl: 'app/pages/empresa/empresa_formview.html',
          controllerAs: 'mp',
          size: 'lg',
          backdropClass: 'splash splash-2 splash-ef-14',
          windowClass: 'splash splash-2 splash-ef-14',          
          /*backdropClass: 'splash splash-ef-14',
          windowClass: 'splash splash-ef-14',*/
          // controller: 'ModalInstanceController',
          controller: function($scope, $uibModalInstance, arrToModal ){
            var vm = this;
            vm.fData = {};
            vm.modoEdicion = false;
            vm.getPaginationServerSide = arrToModal.getPaginationServerSide;
            vm.modalTitle = 'Registro de Empresas';
            // BOTONES
            vm.aceptar = function () {
              EmpresaServices.sRegistrarEmpresa(vm.fData).then(function (rpta) {
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
      /*-------- BOTONES DE EDICION ----*/
      vm.btnEditar = function(row){//datos personales
        var modalInstance = $uibModal.open({
          templateUrl: 'app/pages/empresa/empresa_formview.html',
          controllerAs: 'mp',
          size: 'lg',
          backdropClass: 'splash splash-2 splash-ef-14',
          windowClass: 'splash splash-2 splash-ef-14',
          // controller: 'ModalInstanceController',
          controller: function($scope, $uibModalInstance, arrToModal ){
            var vm = this;
            var openedToasts = [];
            vm.fData = {};
            vm.fData = arrToModal.seleccion;
            console.log("row",vm.fData);
            vm.modoEdicion = true;
            vm.getPaginationServerSide = arrToModal.getPaginationServerSide;

            vm.modalTitle = 'Edición de Empresas';
            //vm.fData.sexo = vm.listaSexos[0].id;
            vm.aceptar = function () {
              console.log('edicion...', vm.fData);
              $uibModalInstance.close(vm.fData);
              EmpresaServices.sEditarEmpresa(vm.fData).then(function (rpta) {
                vm.options = {
                  timeout: '3000',
                  extendedTimeout: '1000',
                  progressBar: true,
                  preventDuplicates: false,
                  preventOpenDuplicates: false
                };
                if(rpta.flag == 1){
                  vm.getPaginationServerSide();
                  var title = 'OK';
                  var iconClass = 'success';
                }else if( rpta.flag == 0 ){
                  var title = 'Advertencia';
                  // vm.toast.title = 'Advertencia';
                  var iconClass = 'warning';
                  // vm.options.iconClass = {name:'warning'}
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
          EmpresaServices.sAnularEmpresa(row.entity).then(function (rpta) {
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
      if(params.param == 'nueva-empresa'){
        vm.btnNuevo();
        //$location.search({param: searchParam});
      }
  }
  function EmpresaServices($http, $q, handle) {
    return({
        sListarEmpresas: sListarEmpresas,
        sListarEmpresaCbo: sListarEmpresaCbo,
        sRegistrarEmpresa: sRegistrarEmpresa,
        sEditarEmpresa: sEditarEmpresa,
        sAnularEmpresa: sAnularEmpresa,
    });
    function sListarEmpresas(pDatos) {
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url :  angular.patchURLCI + "Empresa/listar_empresas",
            data : datos
      });
      return (request.then( handle.success,handle.error ));
    }    
    function sListarEmpresaCbo(pDatos) {
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url :  angular.patchURLCI + "Empresa/listar_empresa_cbo",
            data : datos
      });
      return (request.then( handle.success,handle.error ));
    }
    function sRegistrarEmpresa(pDatos) {
      var datos = pDatos || {};      
      var request = $http({
            method : "post",
            url : angular.patchURLCI + "Empresa/registrar_empresa",
            data : datos
      });
      return (request.then(handle.success,handle.error));
    }   
    function sEditarEmpresa(pDatos) {
      var datos = pDatos || {};      
      var request = $http({
            method : "post",
            url : angular.patchURLCI + "Empresa/editar_empresa",
            data : datos
      });
      return (request.then(handle.success,handle.error));
    }  
    function sAnularEmpresa(pDatos) {
      var datos = pDatos || {};      
      var request = $http({
            method : "post",
            url : angular.patchURLCI + "Empresa/anular_empresa",
            data : datos
      });
      return (request.then(handle.success,handle.error));
    }         
  }
})();
