define( function() {
  'use strict';

  try { var module = cenozoApp.module( 'interview', true ); } catch( err ) { console.warn( err ); return; }
  angular.extend( module, {
    identifier: {},
    name: {
      singular: 'interview',
      plural: 'interviews',
      possessive: 'interview\'s'
    },
    columnList: {
      uid: {
        column: 'participant.uid',
        title: 'UID'
      },
      study_phase: {
        column: 'study_phase.name',
        title: 'Study Phase'
      },
      platform: {
        column: 'platform.name',
        title: 'Platform'
      },
      site: {
        column: 'site.name',
        title: 'Site'
      },
      start_date: {
        title: 'Date',
        type: 'date'
      },
      barcode: {
        title: 'Barcode'
      },
      duration: {
        title: 'Duration',
        type: 'number'
      },
      total_stage_duration: {
        title: 'Total Stage Duration',
        type: 'number'
      }
    },
    defaultOrder: {
      column: 'start_date',
      reverse: true
    }
  } );

  module.addInputGroup( '', {
    uid: {
      column: 'participant.uid',
      title: 'UID',
      type: 'string'
    },
    study_phase: {
      column: 'study_phase.name',
      title: 'Study Phase',
      type: 'string'
    },
    platform: {
      column: 'platform.name',
      title: 'Platform',
      type: 'string'
    },
    site: {
      column: 'site.name',
      title: 'Site',
      type: 'string'
    },
    start_date: {
      title: 'Date',
      type: 'date'
    },
    barcode: {
      title: 'Barcode',
      type: 'string'
    },
    duration: {
      title: 'Duration',
      type: 'string'
    },
    total_stage_duration: {
      title: 'Total Stage Duration',
      type: 'string'
    }
  } );

  /* ######################################################################################################## */
  cenozo.providers.directive( 'cnInterviewList', [
    'CnInterviewModelFactory',
    function( CnInterviewModelFactory ) {
      return {
        templateUrl: module.getFileUrl( 'list.tpl.html' ),
        restrict: 'E',
        scope: { model: '=?' },
        controller: function( $scope ) {
          if( angular.isUndefined( $scope.model ) ) $scope.model = CnInterviewModelFactory.root;
        }
      };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.directive( 'cnInterviewView', [
    'CnInterviewModelFactory',
    function( CnInterviewModelFactory ) {
      return {
        templateUrl: module.getFileUrl( 'view.tpl.html' ),
        restrict: 'E',
        scope: { model: '=?' },
        controller: function( $scope ) {
          if( angular.isUndefined( $scope.model ) ) $scope.model = CnInterviewModelFactory.root;
        }
      };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnInterviewListFactory', [
    'CnBaseListFactory',
    function( CnBaseListFactory ) {
      var object = function( parentModel ) { CnBaseListFactory.construct( this, parentModel ); };
      return { instance: function( parentModel ) { return new object( parentModel ); } };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnInterviewViewFactory', [
    'CnBaseViewFactory',
    function( CnBaseViewFactory ) {
      var object = function( parentModel, root ) { CnBaseViewFactory.construct( this, parentModel, root ); }
      return { instance: function( parentModel, root ) { return new object( parentModel, root ); } };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnInterviewModelFactory', [
    'CnBaseModelFactory', 'CnInterviewListFactory', 'CnInterviewViewFactory',
    function( CnBaseModelFactory, CnInterviewListFactory, CnInterviewViewFactory ) {
      var object = function( root ) {
        var self = this;
        CnBaseModelFactory.construct( this, module );
        this.listModel = CnInterviewListFactory.instance( this );
        this.viewModel = CnInterviewViewFactory.instance( this, root );

        // override the edit functionality (it's used to update data in a nightly script only)
        this.getEditEnabled = function() { return false; }
      };

      return {
        root: new object( true ),
        instance: function() { return new object( false ); }
      };
    }
  ] );

} );
