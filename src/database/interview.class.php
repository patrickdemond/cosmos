<?php
/**
 * interview.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 */

namespace cosmos\database;
use cenozo\lib, cenozo\log, cosmos\util;

/**
 * interview: record
 */
class interview extends \cenozo\database\record
{
  /**
   * Adds any data files from Opal which don't already exist
   * 
   * @access public
   */
  public static function update_interview_list()
  {
    $opal_view_class_name = lib::get_class_name( 'database\opal_view' );
    $participant_class_name = lib::get_class_name( 'database\participant' );
    $interview_class_name = lib::get_class_name( 'database\interview' );
    $stage_class_name = lib::get_class_name( 'database\stage' );
    $stage_type_class_name = lib::get_class_name( 'database\stage_type' );
    $technician_class_name = lib::get_class_name( 'database\technician' );
    $user_class_name = lib::get_class_name( 'database\user' );
    $indicator_class_name = lib::get_class_name( 'database\indicator' );

    $opal_manager = lib::create( 'business\opal_manager' );
    $db_application = lib::create( 'business\session' )->get_application();

    $site_sel = lib::create( 'database\select' );
    $site_sel->add_column( 'id' );
    $site_sel->add_column( 'name' );
    $site_id_list = array();
    foreach( $db_application->get_site_list( $site_sel ) as $site ) $site_id_list[$site['name']] = $site['id'];

    $max_new_interviews = 0;
    foreach( $opal_view_class_name::select_objects() as $db_opal_view )
    {
      $project_name = $db_opal_view->get_project_name();
      $view_name = $db_opal_view->get_view_name();

      if( 'cosmos_inhome' != $project_name ) continue; // TODO: remove this once views are ready

      $count = $opal_manager->count( $project_name, $view_name );
      if( $count > $db_opal_view->total )
      {
        $new_interviews = $count - $db_opal_view->total;
        if( $new_interviews > $max_new_interviews ) $max_new_interviews = $new_interviews;

        log::debug( sprintf( 'Found %s missing records in %s/%s', $new_interviews, $project_name, $view_name ) );
        $values = $opal_manager->get_all_values( $project_name, $view_name, 1000, $db_opal_view->total );
        foreach( $values as $uid => $data )
        {
          $data = util::json_decode( $data['data'] ); // opal has to put the data in a column

          $participant_id = $participant_class_name::get_unique_record( 'uid', $uid )->id;
          $site_id = $site_id_list[$data->site];

          // only add the interview record if it doesn't already exist
          $db_interview = $interview_class_name::get_unique_record(
            array( 'participant_id', 'study_phase_id', 'platform_id' ),
            array( $participant_id, $db_opal_view->study_phase_id, $db_opal_view->platform_id )
          );
          if( is_null( $db_interview ) )
          {
            $db_interview = lib::create( 'database\interview' );
            $db_interview->participant_id = $participant_id;
            $db_interview->study_phase_id = $db_opal_view->study_phase_id;
            $db_interview->platform_id = $db_opal_view->platform_id;
            $db_interview->site_id = $site_id;
            $db_interview->start_date = $data->start_date;
            $db_interview->barcode = $data->barcode;
            $db_interview->duration = $data->duration;
            $db_interview->total_stage_duration = $data->stage_duration;
            $db_interview->save();
          }

          foreach( get_object_vars( $data->stages ) as $stage_name => $stage_data )
          {
            $db_stage_type = $stage_type_class_name::get_unique_record(
              array( 'opal_view_id', 'name' ),
              array( $db_opal_view->id, $stage_name )
            );
            if( is_null( $db_stage_type ) )
            {
              $db_stage_type = lib::create( 'database\stage_type' );
              $db_stage_type->opal_view_id = $db_opal_view->id;
              $db_stage_type->name = $stage_name;
              $db_stage_type->save();
            }

            // see if the stage record already exists
            $db_stage = $stage_class_name::get_unique_record(
              array( 'interview_id', 'stage_type_id' ),
              array( $db_interview->id, $db_stage_type->id )
            );

            if( is_null( $db_stage ) )
            {
              $db_technician = NULL;
              if( property_exists( $stage_data, 'technician' ) )
              {
                // get the technician or create one if they don't already exist
                $db_technician = $technician_class_name::get_unique_record(
                  array( 'site_id', 'name' ),
                  array( $site_id, $stage_data->technician )
                );

                if( is_null( $db_technician ) )
                {
                  $db_technician = lib::create( 'database\technician' );
                  $db_technician->site_id = $site_id;
                  $db_technician->name = $stage_data->technician;

                  // if the user name matches a cenozo user name then link them
                  $db_user = $user_class_name::get_unique_record( 'name', $stage_data->technician );
                  if( !is_null( $db_user ) ) $db_technician->user_id = $db_user->id;

                  $db_technician->save();
                }
                unset( $stage_data->technician );
              }

              $db_stage = lib::create( 'database\stage' );
              $db_stage->interview_id = $db_interview->id;
              $db_stage->stage_type_id = $db_stage_type->id;
              if( !is_null( $db_technician ) ) $db_stage->technician_id = $db_technician->id;

              $db_stage->contraindicated = $stage_data->contraindicated;
              unset( $stage_data->contraindicated );
              
              $db_stage->missing = $stage_data->missing;
              unset( $stage_data->missing );
              
              if( property_exists( $stage_data, 'skip' ) )
              {
                $db_stage->skip = $stage_data->skip;
                unset( $stage_data->skip );
              }

              if( property_exists( $stage_data, 'duration' ) )
              {
                $db_stage->duration = $stage_data->duration;
                unset( $stage_data->duration );
              }

              // encode all remaining data into the JSON data column
              $db_stage->data = util::json_encode( $stage_data );
              $db_stage->save();

              // now create any new indicators in the stage data
              foreach( get_object_vars( $stage_data ) as $name => $value )
              {
                $type = gettype( $value );
                if( 'double' == $type ) $type = 'float';

                $indicator_list = array();
                if( 'object' == $type )
                {
                  // add each individual array element as its own indicator
                  foreach( get_object_vars( $value ) as $k => $v )
                  {
                    $type = gettype( $v );
                    if( 'double' == $type ) $type = 'float';
                    if( 'array' != $type ) $indicator_list[] = array( 'name' => sprintf( '%s.%s', $name, $k ), 'type' => $type );
                  }
                }
                else if( 'array' != $type ) $indicator_list[] = array( 'name' => $name, 'type' => $type );

                foreach( $indicator_list as $indicator )
                {
                  $db_indicator = $indicator_class_name::get_unique_record(
                    array( 'stage_type_id', 'name' ),
                    array( $db_stage_type->id, $indicator['name'] )
                  );

                  if( is_null( $db_indicator ) )
                  {
                    $db_indicator = lib::create( 'database\indicator' );
                    $db_indicator->stage_type_id = $db_stage_type->id;
                    $db_indicator->name = $indicator['name'];
                    $db_indicator->type = $indicator['type'];
                    $db_indicator->save();
                  }
                }
              }
            }
          }
        }
      }
    }

    // update all outliers since we now have new data
    if( 0 < $max_new_interviews ) self::db()->execute( 'CALL update_outliers()' );

    return $max_new_interviews;
  }
}
