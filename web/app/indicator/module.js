define( function() {
  'use strict';

  try { var module = cenozoApp.module( 'indicator', true ); } catch( err ) { console.warn( err ); return; }
  angular.extend( module, {
    identifier: {
      parent: {
        subject: 'stage_type',
        column: 'stage_type_id'
      }
    },
    name: {
      singular: 'indicator',
      plural: 'indicators',
      possessive: 'indicator\'s'
    },
    columnList: {
      name: {
        title: 'Name'
      },
      type: {
        title: 'Type'
      },
      minimum: {
        title: 'Minimum'
      },
      maximum: {
        title: 'Maximum'
      }
    },
    defaultOrder: {
      column: 'indicator.name',
      reverse: false
    }
  } );

  module.addInputGroup( '', {
    study_phase: {
      column: 'study_phase.code',
      title: 'Study Phase',
      type: 'string',
      constant: true
    },
    platform: {
      column: 'platform.name',
      title: 'Platform',
      type: 'string',
      constant: true
    },
    stage_type: {
      column: 'stage_type.name',
      title: 'Stage Type',
      type: 'string',
      constant: true
    },
    name: {
      title: 'Name',
      type: 'string',
      constant: true
    },
    type: {
      title: 'Type',
      type: 'string',
      constant: true
    },
    minimum: {
      title: 'Minimum',
      type: 'string',
      format: 'integer'
    },
    maximum: {
      title: 'Maximum',
      type: 'string',
      format: 'integer'
    },
  } );

  /* ######################################################################################################## */
  cenozo.providers.directive( 'cnIndicatorList', [
    'CnIndicatorModelFactory',
    function( CnIndicatorModelFactory ) {
      return {
        templateUrl: module.getFileUrl( 'list.tpl.html' ),
        restrict: 'E',
        scope: { model: '=?' },
        controller: function( $scope ) {
          if( angular.isUndefined( $scope.model ) ) $scope.model = CnIndicatorModelFactory.root;
        }
      };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.directive( 'cnIndicatorView', [
    'CnIndicatorModelFactory',
    function( CnIndicatorModelFactory ) {
      return {
        templateUrl: module.getFileUrl( 'view.tpl.html' ),
        restrict: 'E',
        scope: { model: '=?' },
        controller: function( $scope ) {
          if( angular.isUndefined( $scope.model ) ) $scope.model = CnIndicatorModelFactory.root;
        }
      };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnIndicatorListFactory', [
    'CnBaseListFactory',
    function( CnBaseListFactory ) {
      var object = function( parentModel ) { CnBaseListFactory.construct( this, parentModel ); };
      return { instance: function( parentModel ) { return new object( parentModel ); } };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnIndicatorViewFactory', [
    'CnBaseViewFactory', 'CnHttpFactory',
    function( CnBaseViewFactory, CnHttpFactory ) {
      var object = function( parentModel, root ) {
        var self = this;
        CnBaseViewFactory.construct( this, parentModel, root );
        this.chartLoading = false;
        this.resetPlot = function() {
          this.chartLoading = true;
          this.plot = {
            labels: [],
            data: [],
            options: {
              scales: {
                xAxes: [ {
                  ticks: {
                    autoSkip: true,
                    maxTicksLimit: 40,
                    callback: function( value, index, values ) {
                      return 1 < values.length && values.length == index+1 ? value + '+' : value;
                    }
                  },
                  scaleLabel: {
                    display: true,
                    labelString: '',
                    fontFamily: 'sans-serif',
                    fontSize: 15
                  }
                } ],
                yAxes: [ {
                  scaleLabel: {
                    display: true,
                    labelString: 'Number of Interviews',
                    fontFamily: 'sans-serif',
                    fontSize: 15
                  }
                } ]
              }
            }
          };
        };

        this.onView = function( force ) {
          this.resetPlot();
          return this.$$onView( force ).then( function() {
            self.plot.options.scales.xAxes[0].scaleLabel.labelString = self.record.name;

            // get all values for the plot
            CnHttpFactory.instance( {
              path: [ self.record.study_phase, self.record.platform, self.record.stage_type, 'data' ].join( '_' ) +
                    '?plot=' + self.record.name
            } ).query().then( function( response ) {
              response.data.forEach( function( row ) {
                self.plot.labels.push( row.value );
                self.plot.data.push( row.count );
              } );
              self.plot.data = [ self.plot.data ];
              self.chartLoading = false;
            } );
          } );
        };
      }
      return { instance: function( parentModel, root ) { return new object( parentModel, root ); } };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnIndicatorModelFactory', [
    'CnBaseModelFactory', 'CnIndicatorListFactory', 'CnIndicatorViewFactory',
    function( CnBaseModelFactory, CnIndicatorListFactory, CnIndicatorViewFactory ) {
      var object = function( root ) {
        var self = this;
        CnBaseModelFactory.construct( this, module );
        this.listModel = CnIndicatorListFactory.instance( this );
        this.viewModel = CnIndicatorViewFactory.instance( this, root );
      };

      return {
        root: new object( true ),
        instance: function() { return new object( false ); }
      };
    }
  ] );

} );
