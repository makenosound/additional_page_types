<?php

	Class extension_additional_page_types extends Extension
	{	
	  
	  public $additional_page_types = array(
		  'CSV',
		  'JSON'
		);
	  
	/*-------------------------------------------------------------------------
		Extension definition
	-------------------------------------------------------------------------*/
		public function about()
		{
			return array('name' => 'Additional Page Types',
						 'version' => '0.1',
						 'release-date' => '2009-03-02',
						 'author' => array('name' => 'Max Wheeler',
										   'website' => 'http://makenosound.com/',
										   'email' => 'max@makenosound.com'),
 						 'description' => 'Adds a few extra options for page output types'
				 		);
		}
		
		public function uninstall()
		{
			# Remove where page_id = 99999999999
			$this->_Parent->Database->delete('tbl_pages_types', "`page_id` = 4294967295");
		}
		
		public function install()
		{
			foreach ($this->additional_page_types as $type)
			{
			  $data = array(
			    "page_id" => 4294967295,
			    "type"    => $type
			    );
			  $this->_Parent->Database->insert($data, 'tbl_pages_types');
			}
		  return true;
		}
		
		public function getSubscribedDelegates()
		{
			return array(
				array(
					'page'		=> '/frontend/',
					'delegate'	=> 'FrontendOutputPreGenerate',
					'callback'	=> 'alter_output_headers'
				),
			);
		}

    public function alter_output_headers($context)
    {
      $page = $context['page'];
      $page_id = $page->_param['current-page-id'];
      $current_page_types = $this->_get_page_types($page_id);
      
      if (in_array('CSV', $current_page_types) && in_array('JSON', $current_page_types))
      {
        $this->_Parent->customError(E_USER_ERROR, 
  											__('Conflicting page types'), 
  											__('You need to select JSON or CSV, not both.'), 
  											false, 
  											true, 
  											'error', 
  											array('header' => 'HTTP/1.0 404 Not Found'));
      }
      else if (in_array("CSV", $current_page_types))
      {
        $page->_headers = array();
        $page->addHeaderToPage('Content-Type', 'text/csv');
        $page->addHeaderToPage('Cache-Control', 'no-store, no-cache');
        $page->addHeaderToPage('Content-Disposition', 'attachment; filename="test.csv"');
      } 
      else if (in_array("JSON", $current_page_types))
      {
        $page->addHeaderToPage('Content-Type', 'application/json; charset=utf-8');
      }
    }

    /*-------------------------------------------------------------------------
    	Helpers
    -------------------------------------------------------------------------*/

    # Replication from class.frontendpage.php
    public function _get_page_types($page_id)
    {
      return $this->_Parent->Database->fetchCol('type', "SELECT `type` FROM `tbl_pages_types` WHERE `page_id` = '{$page_id}' ");
    }
  }