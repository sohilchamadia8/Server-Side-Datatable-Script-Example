	$where_count_clause = $where = '';
	$request = $_GET;
  // print_r($request);
   header("Content-Type: application/json");
  $table_name = $wpdb->prefix."property_defects";
  $table_name_2 = $wpdb->prefix."property_quotation";
  $uid = $user->ID;

  if($request['prop_id']){
  $where_count_clause =  $where .= ' and A.address_id="'.$request['prop_id'].'" ';
  }
  
  
  $offset = $request['start'];
  $limit = $request['length'];

  $columns = array('A.defect_img','A.defect_name','A.defect_type','A.defect_status','B.quotation_id','A.id');

   if( !empty($request['search']['value']) ) { // When datatables search is used
  	$where .= ' and (A.defect_name LIKE "%'.sanitize_text_field($request['search']['value']).'%" or A.defect_status LIKE "%'.sanitize_text_field($request['search']['value']).'%")';
  }

  $order_by = ' order by '.$columns[$request['order'][0]['column']] . ' '. $request['order'][0]['dir'];


   

     $filter_recd = 'SELECT * FROM '.$table_name.' as A LEFT JOIN '.$table_name_2.' as B ON A.id = B.defect_id where 1=1 '.$where.'';
    
    $sql = 'SELECT * FROM '.$table_name.' as A LEFT JOIN '.$table_name_2.' as B ON A.id = B.defect_id where 1=1 '.$where. ' '.$order_by . ' LIMIT '. $offset. ',' .$limit;
	 
	$filter_tot_rcd_obj = $wpdb->get_results($filter_recd);

	$result = $wpdb->get_results($sql);
	 
	foreach ($result as $key => $value) {
		if($value->defect_img != ''){
			$img_arr = explode(',', $value->defect_img);			
			$property_img = $upload_path_arr['baseurl'].'/'.PROPERTY_IMG_FOLDER.'/'.reset($img_arr);
		} else {
			$property_img = $upload_path_arr['baseurl'].'/default.jpg';
		}
		$nestedData = array();
	  $nestedData[] = '<img class="card-img-top img-fluid cstm_exp_proper_img" src="'.$property_img .'" alt="Property">';
      $nestedData[] = $value->defect_name;
      $nestedData[] = $value->defect_type;
      $html_status = html_status_based_on_defect_status($value->defect_status);
      $nestedData[] = $html_status;
      if($value->quotation_id == ''){
        $quotation_html = '<a href="javascript:void(0);" class="cstm_add_quotation" data-defect_id="'.$value->id.'" data-defect_name="'.$value->defect_name.'"><i class="fa fa-plus-circle" aria-hidden="true"></i>Add Quotation</a>'; 
      } else {
        $quotation_html = '<a href="javascript:void(0);" class="cstm_view_quotation" data-defect_id="'.$value->id.'"><i class="fa fa-eye" aria-hidden="true"></i>View Quotation</a>';
      }
      $nestedData[] = $quotation_html;
    
      
      $action_button = '<a href="javascript:void(0);" class="cstm_defect_view" data-defect_id="'.$value->id.'"><i class="fa fa-eye" aria-hidden="true"></i>View Detail</a>';

      if($value->defect_status == 'active'){
        $action_button .= '&nbsp; <a href="javascript:void(0);" class="cstm_defect_complete_status" data-defect_id="'.$value->id.'" data-defect_name="'.$value->defect_name.'"><i class="fa fa-check-circle" aria-hidden="true"></i>Click to Complete</a>';
      }

      $nestedData[] = $action_button;

      $data[] =$nestedData;
	}
	 



  $count_query = 'SELECT count(*) FROM '.$table_name.' as A LEFT JOIN '.$table_name_2.' as B ON A.id = B.defect_id where 1=1 '.$where_count_clause.'';


    $num = $wpdb->get_var($count_query);
    
  if ( $data ) {

    $json_data = array(
      "draw" => intval($request['draw']),
      "recordsTotal" => intval($num),
      "recordsFiltered" => intval(count($filter_tot_rcd_obj)),
      "data" => $data
    );

    echo json_encode($json_data);

  } else {

      $json_data = array(
      "draw" => intval($request['draw']),
      "recordsTotal" => intval($num),
      "recordsFiltered" => intval(count($filter_tot_rcd_obj)),
      "data" => array()
    );

    echo json_encode($json_data);
  }
  
  wp_die();