define( [ 'carotid_intima_data', 'ecg_data' ].reduce( function( list, name ) {
  return list.concat( cenozoApp.module( name ).getRequiredFiles() );
}, [] ), function () {
  'use strict';

  try { var module = cenozoApp.module( 'stage', true ); } catch( err ) { console.warn( err ); return; }
  angular.extend( module, {
    identifier: {},
    name: {
      singular: 'stage',
      plural: 'stages',
      possessive: 'stage\'s'
    },
    columnList: {
      stage_type: {
        column: 'stage_type.name',
        title: 'Stage Type'
      },
      technician: {
        column: 'technician.name',
        title: 'Technician'
      },
      contraindicated: {
        title: 'Contraindicated',
        type: 'boolean'
      },
      missing: {
        title: 'Missing',
        type: 'boolean'
      },
      skip: {
        title: 'Skip'
      },
      duration: {
        title: 'Duration',
        type: 'number'
      }
    },
    defaultOrder: {
      column: 'stage_type.name',
      reverse: false
    }
  } );

  module.addInputGroup( '', {
    stage_type: {
      column: 'stage_type.name',
      title: 'Stage Type',
      type: 'string'
    },
    technician: {
      column: 'technician.name',
      title: 'Technician',
      type: 'string'
    },
    contraindicated: {
      title: 'Contraindicated',
      type: 'boolean'
    },
    missing: {
      title: 'Missing',
      type: 'boolean'
    },
    skip: {
      title: 'Skip',
      type: 'string'
    },
    duration: {
      title: 'Duration',
      type: 'string',
      format: 'float'
    }
  } );

  /* ######################################################################################################## */
  cenozo.providers.directive( 'cnStageList', [
    'CnStageModelFactory',
    function( CnStageModelFactory ) {
      return {
        templateUrl: module.getFileUrl( 'list.tpl.html' ),
        restrict: 'E',
        scope: { model: '=?' },
        controller: function( $scope ) {
          if( angular.isUndefined( $scope.model ) ) $scope.model = CnStageModelFactory.root;
        }
      };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.directive( 'cnStageView', [
    'CnStageModelFactory',
    function( CnStageModelFactory ) {
      return {
        templateUrl: module.getFileUrl( 'view.tpl.html' ),
        restrict: 'E',
        scope: { model: '=?' },
        controller: function( $scope ) {
          if( angular.isUndefined( $scope.model ) ) $scope.model = CnStageModelFactory.root;
        }
      };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnStageListFactory', [
    'CnBaseListFactory',
    function( CnBaseListFactory ) {
      var object = function( parentModel ) { CnBaseListFactory.construct( this, parentModel ); };
      return { instance: function( parentModel ) { return new object( parentModel ); } };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnStageViewFactory', [
    'CnBaseViewFactory', 'CnHttpFactory',
    'CnCarotidIntimaDataModelFactory', 'CnEcgDataModelFactory',
    function( CnBaseViewFactory, CnHttpFactory,
              CnCarotidIntimaDataModelFactory, CnEcgDataModelFactory ) {
      var object = function( parentModel, root ) {
        var self = this;
        this.indicatorList = [];
        CnBaseViewFactory.construct( this, parentModel, root );

        this.onView = function( force ) {
          self.dataModel = null;
          return this.$$onView( force ).then( function() {
            self.dataModel = eval( 'Cn' + self.record.stage_type.snakeToCamel().ucWords() + 'DataModelFactory' ).root;
            self.dataModel.viewModel.onView( true );
          } );
        };
      }
      return { instance: function( parentModel, root ) { return new object( parentModel, root ); } };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnStageModelFactory', [
    'CnBaseModelFactory', 'CnStageListFactory', 'CnStageViewFactory',
    function( CnBaseModelFactory, CnStageListFactory, CnStageViewFactory ) {
      var object = function( root ) {
        var self = this;
        CnBaseModelFactory.construct( this, module );
        this.listModel = CnStageListFactory.instance( this );
        this.viewModel = CnStageViewFactory.instance( this, root );
      };

      return {
        root: new object( true ),
        instance: function() { return new object( false ); }
      };
    }
  ] );

} );
