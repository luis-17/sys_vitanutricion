(function() {
  'use strict';

  angular
    .module('minotaur')
    .config(routerConfig);

  /** @ngInject */
  function routerConfig($stateProvider, $urlRouterProvider) {
    $stateProvider
      //dashboard
      .state('dashboard', {
        url: '/app/dashboard',
        templateUrl: 'app/pages/dashboard/dashboard.html',
        controller: 'DashboardController',
        controllerAs: 'ds'
      })
      //app core pages (errors, login,signup)
      .state('pages', {
        url: '/app/pages',
        template: '<div ui-view></div>'
      })
      //login
      .state('pages.login', { 
        url: '/login',
        templateUrl: 'app/pages/pages-login/pages-login.html',
        controller: 'LoginController',
        controllerAs: 'ctrl',
        parent: 'pages',
        specialClass: 'core'
      })
      //empresa
      .state('empresa', {
        url: '/app/empresa',
        templateUrl: 'app/pages/empresa/empresa.html',
        controller: 'EmpresaController',
        controllerAs: 'emp'
      })
      //paciente
      .state('paciente', {   
        url: '/app/paciente',
        templateUrl: 'app/pages/paciente/paciente.html',
        controller: 'PacienteController',
        controllerAs: 'pac' ,
        params: {
          search : false
        }        
      })
      .state('pacienteficha', {
        url: '/app/paciente/ficha',
        templateUrl: 'app/pages/paciente/paciente.html',
        controller: 'PacienteController',
        controllerAs: 'pac' ,
        params: {
          search : true
        }
      })      
      //citas
      .state('citas', {
        url: '/app/citas',
        templateUrl: 'app/pages/citas/citas.html',
        controller: 'CitasController as vm'
      })    
      //alimentos 
      .state('alimento', { 
        url: '/app/alimento',
        templateUrl: 'app/pages/alimento/alimento.html',
        controller: 'AlimentoController as vm'
      })
      //profesionales 
      .state('profesional', { 
        url: '/app/profesional',
        templateUrl: 'app/pages/profesional/profesional.html',
        controller: 'ProfesionalController as pro'
      })
      //alimentos 
      .state('informe-empresarial', { 
        url: '/app/informe-empresarial',
        templateUrl: 'app/pages/informe/informe-empresarial.html',
        controller: 'InformeEmpresarialController as vm'
      });
    $urlRouterProvider.otherwise('/app/dashboard'); 

    
  }

})();
