(function() {
  'use strict';
  
  if (!window.location.origin) {
    window.location.origin = window.location.protocol+"//"+window.location.host;
  }
  var dirWebRoot =  window.location.origin + '/'+directoryApp+'/';
  angular.patchURLCI = dirWebRoot+'ci.php/';

  angular
    .module('minotaur')
    .config(config);

  /** @ngInject */
  function config($logProvider, toastrConfig, $translateProvider, $locationProvider,$qProvider,$stateProvider, $urlRouterProvider) { 
    // Enable log
    $logProvider.debugEnabled(true);
    // Set options third-party lib
    toastrConfig.allowHtml = true;
    toastrConfig.timeOut = 3000;
    toastrConfig.positionClass = 'toast-top-right';
    toastrConfig.preventDuplicates = true;
    toastrConfig.progressBar = true;

    // $qProvider.errorOnUnhandledRejections(false);

    // angular-language
    $translateProvider.useStaticFilesLoader({
      prefix: 'assets/languages/',
      suffix: '.json'
    });
    $translateProvider.useLocalStorage();
    $translateProvider.preferredLanguage('es');
    $translateProvider.useSanitizeValueStrategy(null);

    // remove ! hash prefix
    $locationProvider.hashPrefix('');

  }

})();

function handleError(error) {
  return function () {
    return {success: false, message: Notification.warning({message: error})};
  };
}
function handleSuccess(response) {
    return( response.data );
}
