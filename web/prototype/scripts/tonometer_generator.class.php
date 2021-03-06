<?php
require_once 'table_generator.class.php';

class tonometer_generator extends table_generator
{
  public function __construct($_stage, $_rank, $_begin_date = null, $_end_date = null)
  {
    parent::__construct($_stage, $_rank, $_begin_date, $_end_date);

    $this->indicator_keys=array(
      'total_quality_low_left','total_quality_medium_left','total_quality_high_left',
      'total_quality_low_right','total_quality_medium_right','total_quality_high_right');
    $this->indicator_keys[]='total_curve_left';
    $this->indicator_keys[]='total_curve_right';

    //$this->percent_keys = array('total_curve_left','total_curve_right') + $this->percent_keys;

    $this->group_indicator_keys=array(
      'left'=>array('total_quality_low_left','total_quality_medium_left','total_quality_high_left'),
      'right'=>array('total_quality_low_right','total_quality_medium_right','total_quality_high_right'),
      'both'=>array('total_curve_left','total_curve_right')
      );
  }

  protected function build_data()
  {
    global $db;

    // build the main query
    $sql =
      'select '.
      'ifnull(t.name,"NA") as tech, '.
      'site.name as site, ';

    $sql .=
      'sum(if(qcdata is null, 0, '.
      'if(cast(substring_index(substring_index(qcdata,",",2),":",-1) as decimal(10,3))<2.5,1,0))) as total_quality_low_left, ';

    $sql .=
      'sum(if(qcdata is null, 0, '.
      'if(cast(substring_index(substring_index(qcdata,",",2),":",-1) as decimal(10,3)) between 2.5 and 7.5,1,0))) as total_quality_medium_left, ';

    $sql .=
      'sum(if(qcdata is null, 0, '.
      'if(cast(substring_index(substring_index(qcdata,",",2),":",-1) as decimal(10,3))>7.5,1,0))) as total_quality_high_left, ';

    $sql .=
      'sum(if(qcdata is null, 0, '.
      'if(cast(substring_index(substring_index(qcdata,",",6),":",-1) as decimal(10,3))<2.5,1,0))) as total_quality_low_right, ';

    $sql .=
      'sum(if(qcdata is null, 0, '.
      'if(cast(substring_index(substring_index(qcdata,",",6),":",-1) as decimal(10,3)) between 2.5 and 7.5,1,0))) as total_quality_medium_right, ';

    $sql .=
      'sum(if(qcdata is null, 0, '.
      'if(cast(substring_index(substring_index(qcdata,",",6),":",-1) as decimal(10,3))>7.5,1,0))) as total_quality_high_right, ';

    $sql .=
      'sum(if(qcdata is null, 0, '.
      'if(cast(substring_index(substring_index(qcdata,",",3),":",-1) as signed)=400 AND '.
      '   cast(substring_index(substring_index(qcdata,",",4),":",-1) as signed)=400,1,0))) as total_curve_left, ';

    $sql .=
      'sum(if(qcdata is null, 0, '.
      'if(cast(substring_index(substring_index(qcdata,",",7),":",-1) as signed)=400 AND '.
      '   cast(trim("}" from substring_index(substring_index(qcdata,",",8),":",-1)) as signed)=400,1,0))) as total_curve_right, ';

    $sql .= $this->get_main_query();

    $res = $db->get_all( $sql );
    if(false===$res || !is_array($res))
    {
      echo sprintf('error: failed query: %s', $db->get_last_error());
      echo $sql;
      die();
    }
    $this->data = $res;

    $this->page_explanation=array();
    $this->page_explanation[]='tonometer quality index 0 - 10';
    $this->page_explanation[]='subpar quality: q < 2.5';
    $this->page_explanation[]='par quality: 2.5 <  q < 7.5';
    $this->page_explanation[]='above par quality: q > 7.5';
    $this->page_explanation[]='curve availability if both pressure and applanation data are present';
  }
/*
  public function build_table_data()
  {
    parent::build_table_data();

    $this->indicator_keys[]='total_curve_left';
    $this->indicator_keys[]='total_curve_right';
  }
*/  
}
