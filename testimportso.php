<?php
class TestImportSO
{
	// change this to either stage or local
	protected $destination = 'local';

	// number of n latest SOs to be processed
	protected $num_latest_so = 100;

	// DB config for production
	protected $prodhost = 'bilnadbma.csgheamo30fd.ap-southeast-1.rds.amazonaws.com';
	protected $produser = 'mybilnauser';
	protected $prodpassword = 'LawnqUiT79';
	protected $proddb = 'bilna_live';

	// DB config for stage (Pokey)
	protected $stagehost = 'bilnastage.csgheamo30fd.ap-southeast-1.rds.amazonaws.com';
	protected $stageuser = 'mybilnauser';
	protected $stagepassword = 'LawnqUiT79';
	protected $stagedb = 'bilna_live_stagey';

	// DB config for local
	protected $localhost = 'localhost';
	protected $localuser = 'root';
	protected $localpassword = '';
	protected $localdb = 'bilna';

	// array variables for storing field names of tables
	protected $sales_flat_order_fields = array();
	protected $sales_flat_order_item_fields = array();
	protected $sales_flat_order_grid_fields = array();
	protected $sales_flat_order_payment_fields = array();
	protected $sales_flat_order_address_fields = array();
	protected $sales_flat_order_status_history_fields = array();
	protected $sales_flat_invoice_fields = array();
	protected $sales_flat_invoice_item_fields = array();
	protected $sales_flat_invoice_grid_fields = array();

	// array variables for storing values of tables
	protected $sales_flat_order_values = array();
	protected $sales_flat_order_item_values = array();
	protected $sales_flat_order_grid_values = array();
	protected $sales_flat_order_payment_values = array();
	protected $sales_flat_order_address_values = array();
	protected $sales_flat_order_status_history_values = array();
	protected $sales_flat_invoice_values = array();
	protected $sales_flat_invoice_item_values = array();
	protected $sales_flat_invoice_grid_values = array();

	// array variables for SO entity ID mapping (production to stage/local)
	protected $sales_entity_id_mapper = array();

	// array variables for invoice entity ID mapping (production to stage/local)
	protected $invoice_entity_id_mapper = array();

	// array variables for sales item ID mapping (production to stage/local)
	protected $sales_item_id_mapper = array();

	// hard coded section
	protected $customer_id_hardcoded = 95503;
	protected $customer_address_id_hardcoded = 77823;
	protected $increment_id_suffix = '-3';

	// fields that are excepted to be inserted
	protected $fields_excepted = ['sales_flat_order' => ['entity_id', 'cod', 'express_shipping'], 'sales_flat_order_payment' => ['entity_id'],
		'sales_flat_invoice' => ['entity_id'], 'sales_flat_order_item' => ['item_id', 'cod', 'express_shipping', 'cross_border'], 'sales_flat_order_address' => ['entity_id'],
		'sales_flat_invoice_item' => ['entity_id']];

	public function set_server($server)
	{
		$this->destination = $server;
	}

	public function set_customer_id($customer_id)
	{
		$this->customer_id_hardcoded = $customer_id;
	}

	public function set_customer_address_id($customer_address_id)
	{
		$this->customer_address_id_hardcoded = $customer_address_id;
	}

	public function set_no_sos($no_sos)
	{
		$this->num_latest_so = $no_sos;
	}

	public function run()
	{
		$prodlink = $this->connect_production();
		// $this->retrieve_fields_from_tables($prodlink); // comment temporarily

		// connection is either local or stage
		if ($this->destination == 'local')
			$destlink = $this->connect_local();
		else
		if ($this->destination == 'stage')
			$destlink = $this->connect_stage();
		else
		{
			echo "DESTINATION ERROR : MUST BE LOCAL OR STAGE ONLY";
			exit;
		}

		/* begin process copying */
		$this->copy_so_table($prodlink, $destlink);
		$this->copy_sales_flat_order_grid($prodlink, $destlink);
		$this->copy_sales_flat_order_item($prodlink, $destlink);
		$this->copy_sales_flat_order_address($prodlink, $destlink);
		$this->copy_sales_flat_order_payment($prodlink, $destlink);
		$this->copy_sales_flat_invoice($prodlink, $destlink);
		$this->copy_sales_flat_invoice_grid($prodlink, $destlink);
		$this->copy_sales_flat_invoice_item($prodlink, $destlink);

		/* copy to table message */
		$this->copy_table_message($destlink);

		if (count($this->sales_entity_id_mapper) > 0)
		{
			echo '<h3>Sales That Are Successfully Imported From Production</h3>';
			echo '<table><tr><th>No</th><th>Sales Entity ID</th><th>Sales Order No</th></tr>';
			$result = mysqli_query($destlink, "SELECT * FROM sales_flat_order WHERE entity_id IN ({$this->convertValuesToCommas($this->sales_entity_id_mapper)})");
			if (!$result) {
			    die('Invalid query: ' . mysqli_error($destlink));
			}

			if (count($result) > 0)
			{
				$no = 1;
				while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC) ) {
					echo '<tr>';
					echo '<td>'.$no.'</td>';
					echo '<td>'.$row['entity_id'].'</td>';
					echo '<td>'.$row['increment_id'].'</td>';
					echo '</tr>';
					$no++;
				}
			}
			echo '</table>';
		}

		echo '<br /><br />';

		if (count($this->invoice_entity_id_mapper) > 0)
		{
			echo '<h3>Invoices That Are Successfully Imported From Production</h3>';
			echo '<table><tr><th>No</th><th>Invoice Entity ID</th><th>Invoice No</th></tr>';
			$result = mysqli_query($destlink, "SELECT * FROM sales_flat_invoice WHERE entity_id IN ({$this->convertValuesToCommas($this->invoice_entity_id_mapper)})");
			if (!$result) {
			    die('Invalid query: ' . mysqli_error($destlink));
			}

			if (count($result) > 0)
			{
				$no = 1;
				while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC) ) {
					echo '<tr>';
					echo '<td>'.$no.'</td>';
					echo '<td>'.$row['entity_id'].'</td>';
					echo '<td>'.$row['increment_id'].'</td>';
					echo '</tr>';
					$no++;
				}
			}
			echo '</table>';
		}

		mysqli_close($prodlink);
		mysqli_close($destlink);
	}

	private function connect_production()
	{
		$link = mysqli_connect($this->prodhost, $this->produser, $this->prodpassword, $this->proddb);
		if (!$link) {
		    die('Could not connect to Production : ' . mysqli_error($link));
		    exit;
		}

		return $link;
	}

	private function connect_stage()
	{
		$link = mysqli_connect($this->stagehost, $this->stageuser, $this->stagepassword, $this->stagedb);
		if (!$link) {
		    die('Could not connect to Stage : ' . mysqli_error($link));
		    exit;
		}

		return $link;
	}

	private function connect_local()
	{
		$link = mysqli_connect($this->localhost, $this->localuser, $this->localpassword, $this->localdb);
		if (!$link) {
		    die('Could not connect to Local : ' . mysqli_error($link));
		    exit;
		}

		return $link;
	}

	private function retrieve_fields_from_tables($link)
	{
		$this->get_fields($link, 'sales_flat_order', 'sales_flat_order_fields');
		$this->get_fields($link, 'sales_flat_order_address', 'sales_flat_order_address_fields');
		$this->get_fields($link, 'sales_flat_order_item', 'sales_flat_order_item_fields');
		$this->get_fields($link, 'sales_flat_order_grid', 'sales_flat_order_grid_fields');
		$this->get_fields($link, 'sales_flat_order_payment', 'sales_flat_order_payment_fields');
		$this->get_fields($link, 'sales_flat_order_status_history', 'sales_flat_order_status_history_fields');
		$this->get_fields($link, 'sales_flat_invoice', 'sales_flat_invoice_fields');
		$this->get_fields($link, 'sales_flat_invoice_item', 'sales_flat_invoice_item_fields');
		$this->get_fields($link, 'sales_flat_invoice_grid', 'sales_flat_invoice_grid_fields');
		$this->get_fields($link, 'sales_flat_shipment', 'sales_flat_shipment_fields');
		$this->get_fields($link, 'sales_flat_shipment_grid', 'sales_flat_shipment_grid_fields');
		$this->get_fields($link, 'sales_flat_shipment_item', 'sales_flat_shipment_item_fields');
		$this->get_fields($link, 'sales_flat_shipment_track', 'sales_flat_shipment_track_fields');
	}

	private function get_fields($link, $table, $array_field_var)
	{
		$fields = array();
		$result = mysqli_query($link, "SELECT * FROM $table LIMIT 0, 1");
		if (!$result) {
		    die('Invalid query: ' . mysqli_error($link));
		}

		if (count($result) > 0)
		{
			while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC) ) {  
				foreach($row as $key => $value)
					$fields[] = $key;

				break;
			}

			$this->{$array_field_var} = $fields;
		}
	}

	/* PROCESSING SALES ORDER */
	/* --------------------------------------------------------------------------------------------------- */
	private function copy_so_table($prodlink, $destlink)
	{
		echo "BEGIN COPYING SO FROM PRODUCTION<br />";
		echo "==========================================================<br /><br />";

		$data = array();
		$result = mysqli_query($prodlink, "SELECT * FROM sales_flat_order ORDER BY entity_id DESC LIMIT 0, {$this->num_latest_so}");
		if (!$result) {
		    die('Invalid query: ' . mysqli_error($prodlink));
		}

		$already_obtain_headers = false;
		$colHeaders = '';

		if (count($result) > 0)
		{
			while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC) ) { 
				$row['customer_id'] = $this->customer_id_hardcoded; // hard code to someone's id
				$row['increment_id'] = $row['increment_id'] . $this->increment_id_suffix; // add suffix
				$row['netsuite_internal_id'] = ''; // empty the netsuite internal id
				if (!$already_obtain_headers)
				{
					$colHeaders = $this->writeColHeaders($row, 'sales_flat_order');
					$already_obtain_headers = true;
				}
				$data[] = $row;
			}
		}

		// insert into destination table
		foreach($data as $key => $values)
		{
			// insert into destination table
			$insertQuery = "INSERT INTO sales_flat_order ({$colHeaders}) VALUES ";
			$insertQuery .= '(' . $this->getColValues($values, 'sales_flat_order') . ')';

			$result = mysqli_query($destlink, $insertQuery);
			if (!$result) 
			    die('Error insert: ' . mysqli_error($destlink));
			else
				$this->sales_entity_id_mapper[$values['entity_id']] = mysqli_insert_id($destlink);
		}

		echo "<br />==========================================================<br />";
		echo "FINISH COPYING SO FROM PRODUCTION<br /><br />";
	}

	private function copy_sales_flat_order_grid($prodlink, $destlink)
	{
		echo "BEGIN COPYING SO GRID FROM PRODUCTION<br />";
		echo "==========================================================<br /><br />";

		if (count($this->sales_entity_id_mapper) < 1)
			return;

		$data = array();
		$result = mysqli_query($prodlink, "SELECT * FROM sales_flat_order_grid WHERE entity_id IN ({$this->convertKeysToCommas($this->sales_entity_id_mapper)})");
		if (!$result) {
		    die('Invalid query: ' . mysqli_error($prodlink));
		}

		$already_obtain_headers = false;
		$colHeaders = '';

		if (count($result) > 0)
		{
			while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC) ) {
				$row['entity_id'] = $this->sales_entity_id_mapper[$row['entity_id']]; // replace the entity ID from the mapper
				$row['customer_id'] = $this->customer_id_hardcoded; // hard code to someone's id
				$row['increment_id'] = $row['increment_id'] . $this->increment_id_suffix; // add suffix
				if (!$already_obtain_headers)
				{
					$colHeaders = $this->writeColHeaders($row);
					$already_obtain_headers = true;
				}
				$data[] = $row;
			}
		}

		// insert into destination table
		foreach($data as $key => $values)
		{
			// insert into destination table
			$insertQuery = "INSERT INTO sales_flat_order_grid ({$colHeaders}) VALUES ";
			$insertQuery .= '(' . $this->getColValues($values) . ')';

			$result = mysqli_query($destlink, $insertQuery);
			if (!$result) 
			    die('Error insert: ' . mysqli_error($destlink));
		}

		echo "<br />==========================================================<br />";
		echo "FINISH COPYING SO GRID FROM PRODUCTION<br /><br />";
	}

	private function copy_sales_flat_order_item($prodlink, $destlink)
	{
		echo "BEGIN COPYING SO ITEM FROM PRODUCTION<br />";
		echo "==========================================================<br /><br />";

		if (count($this->sales_entity_id_mapper) < 1)
			return;

		$data = array();
		$result = mysqli_query($prodlink, "SELECT * FROM sales_flat_order_item WHERE order_id IN ({$this->convertKeysToCommas($this->sales_entity_id_mapper)}) ORDER BY item_id");
		if (!$result) {
		    die('Invalid query: ' . mysqli_error($prodlink));
		}

		$already_obtain_headers = false;
		$colHeaders = '';

		if (count($result) > 0)
		{
			while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC) ) {
				$row['order_id'] = $this->sales_entity_id_mapper[$row['order_id']]; // replace the order ID from the mapper
				if (!$already_obtain_headers)
				{
					$colHeaders = $this->writeColHeaders($row, 'sales_flat_order_item');
					$already_obtain_headers = true;
				}
				$data[] = $row;
			}
		}

		$already_get_first_item_id = false;
		$first_item_id = 0;

		// insert into destination table
		foreach($data as $key => $values)
		{
			// insert into destination table
			$insertQuery = "INSERT INTO sales_flat_order_item ({$colHeaders}) VALUES ";
			$insertQuery .= '(' . $this->getColValues($values, 'sales_flat_order_item') . ')';

			$result = mysqli_query($destlink, $insertQuery);
			if (!$result) 
			    die('Error insert: ' . mysqli_error($destlink));
			else
			{
				$this->sales_item_id_mapper[$values['item_id']] = mysqli_insert_id($destlink);
				if (!$already_get_first_item_id)
				{
					$first_item_id = $this->sales_item_id_mapper[$values['item_id']];
					$already_get_first_item_id = true;
				}
			}
		}

		// then, we have to update the parent_item_id according to the item_id of destination table
		foreach($this->sales_item_id_mapper as $key => $value)
		{
			// update parent item ID
			$updateQuery = "UPDATE sales_flat_order_item SET parent_item_id = $value WHERE parent_item_id = $key AND item_id >= $first_item_id";

			$result = mysqli_query($destlink, $updateQuery);
			if (!$result) 
			    die('Error update: ' . mysqli_error($destlink));
		}

		echo "<br />==========================================================<br />";
		echo "FINISH COPYING SO ITEM FROM PRODUCTION<br /><br />";
	}

	private function copy_sales_flat_order_address($prodlink, $destlink)
	{
		echo "BEGIN COPYING SO ADDRESS FROM PRODUCTION<br />";
		echo "==========================================================<br /><br />";

		if (count($this->sales_entity_id_mapper) < 1)
			return;

		$data = array();
		$result = mysqli_query($prodlink, "SELECT * FROM sales_flat_order_address WHERE parent_id IN ({$this->convertKeysToCommas($this->sales_entity_id_mapper)})");
		if (!$result) {
		    die('Invalid query: ' . mysqli_error($prodlink));
		}

		$already_obtain_headers = false;
		$colHeaders = '';

		if (count($result) > 0)
		{
			while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC) ) {
				$row['parent_id'] = $this->sales_entity_id_mapper[$row['parent_id']]; // replace the parent ID from the mapper
				$row['customer_id'] = $this->customer_id_hardcoded;
				$row['customer_address_id'] = $this->customer_address_id_hardcoded;
				if (!$already_obtain_headers)
				{
					$colHeaders = $this->writeColHeaders($row, 'sales_flat_order_address');
					$already_obtain_headers = true;
				}
				$data[] = $row;
			}
		}

		// insert into destination table
		foreach($data as $key => $values)
		{
			// insert into destination table
			$insertQuery = "INSERT INTO sales_flat_order_address ({$colHeaders}) VALUES ";
			$insertQuery .= '(' . $this->getColValues($values, 'sales_flat_order_address') . ')';

			$result = mysqli_query($destlink, $insertQuery);
			if (!$result) 
			    die('Error insert: ' . mysqli_error($destlink));

			$address_type_id = mysqli_insert_id($destlink);

			// update the sales order's billing address id and shipping address id
			if ($values['address_type'] == 'shipping')
				$updateQuery = "UPDATE sales_flat_order SET shipping_address_id = $address_type_id WHERE entity_id = {$values['parent_id']}";
			else
			if ($values['address_type'] == 'billing')
				$updateQuery = "UPDATE sales_flat_order SET billing_address_id = $address_type_id WHERE entity_id = {$values['parent_id']}";
			
			$result = mysqli_query($destlink, $updateQuery);
			if (!$result) 
			    die('Error update: ' . mysqli_error($destlink));
		}

		echo "<br />==========================================================<br />";
		echo "FINISH COPYING SO ADDRESS FROM PRODUCTION<br /><br />";
	}

	private function copy_sales_flat_order_payment($prodlink, $destlink)
	{
		echo "BEGIN COPYING SO PAYMENT FROM PRODUCTION<br />";
		echo "==========================================================<br /><br />";

		if (count($this->sales_entity_id_mapper) < 1)
			return;

		$data = array();
		$result = mysqli_query($prodlink, "SELECT * FROM sales_flat_order_payment WHERE parent_id IN ({$this->convertKeysToCommas($this->sales_entity_id_mapper)})");
		if (!$result) {
		    die('Invalid query: ' . mysqli_error($prodlink));
		}

		$already_obtain_headers = false;
		$colHeaders = '';

		if (count($result) > 0)
		{
			while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC) ) {
				$row['parent_id'] = $this->sales_entity_id_mapper[$row['parent_id']]; // replace the parent ID from the mapper
				if (!$already_obtain_headers)
				{
					$colHeaders = $this->writeColHeaders($row, 'sales_flat_order_payment');
					$already_obtain_headers = true;
				}
				$data[] = $row;
			}
		}

		// insert into destination table
		foreach($data as $key => $values)
		{
			// insert into destination table
			$insertQuery = "INSERT INTO sales_flat_order_payment ({$colHeaders}) VALUES ";
			$insertQuery .= '(' . $this->getColValues($values, 'sales_flat_order_payment') . ')';

			$result = mysqli_query($destlink, $insertQuery);
			if (!$result) 
			    die('Error insert: ' . mysqli_error($destlink));
		}

		echo "<br />==========================================================<br />";
		echo "FINISH COPYING SO PAYMENT FROM PRODUCTION<br /><br />";
	}

	/* PROCESSING INVOICE */
	/* --------------------------------------------------------------------------------------------------- */
	private function copy_sales_flat_invoice($prodlink, $destlink)
	{
		echo "BEGIN COPYING INVOICE FROM PRODUCTION<br />";
		echo "==========================================================<br /><br />";

		$data = array();
		$result = mysqli_query($prodlink, "SELECT * FROM sales_flat_invoice WHERE order_id IN ({$this->convertKeysToCommas($this->sales_entity_id_mapper)})");
		if (!$result) {
		    die('Invalid query: ' . mysqli_error($prodlink));
		}

		$already_obtain_headers = false;
		$colHeaders = '';

		if (count($result) > 0)
		{
			while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC) ) {
				$row['order_id'] = $this->sales_entity_id_mapper[$row['order_id']]; // replace the order ID from the mapper
				$row['increment_id'] = $row['increment_id'] . $this->increment_id_suffix; // add suffix
				$row['netsuite_internal_id'] = ''; // empty the netsuite internal id

				// get billing address id and shipping address id from sales order
				$address_type_ids = $this->get_address_type_ids($row['order_id'], $destlink);
				$row['billing_address_id'] = $address_type_ids['billing_address_id'];
				$row['shipping_address_id'] = $address_type_ids['shipping_address_id'];
				if (!$already_obtain_headers)
				{
					$colHeaders = $this->writeColHeaders($row, 'sales_flat_invoice');
					$already_obtain_headers = true;
				}
				$data[] = $row;
			}
		}

		// insert into destination table
		foreach($data as $key => $values)
		{
			// insert into destination table
			$insertQuery = "INSERT INTO sales_flat_invoice ({$colHeaders}) VALUES ";
			$insertQuery .= '(' . $this->getColValues($values, 'sales_flat_invoice') . ')';

			$result = mysqli_query($destlink, $insertQuery);
			if (!$result) 
			    die('Error insert: ' . mysqli_error($destlink));
			else
				$this->invoice_entity_id_mapper[$values['entity_id']] = mysqli_insert_id($destlink);
		}

		echo "<br />==========================================================<br />";
		echo "FINISH COPYING INVOICE FROM PRODUCTION<br /><br />";
	}

	private function copy_sales_flat_invoice_grid($prodlink, $destlink)
	{
		echo "BEGIN COPYING INVOICE GRID FROM PRODUCTION<br />";
		echo "==========================================================<br /><br />";

		if (count($this->invoice_entity_id_mapper) < 1)
			return;

		$data = array();
		$result = mysqli_query($prodlink, "SELECT * FROM sales_flat_invoice_grid WHERE entity_id IN ({$this->convertKeysToCommas($this->invoice_entity_id_mapper)})");
		if (!$result) {
		    die('Invalid query: ' . mysqli_error($prodlink));
		}

		$already_obtain_headers = false;
		$colHeaders = '';

		if (count($result) > 0)
		{
			while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC) ) {
				$row['entity_id'] = $this->invoice_entity_id_mapper[$row['entity_id']]; // replace the entity ID from the mapper
				$row['order_id'] = $this->sales_entity_id_mapper[$row['order_id']]; // replace the order ID from the mapper
				$row['increment_id'] = $row['increment_id'] . $this->increment_id_suffix; // add suffix
				$row['order_increment_id'] = $row['order_increment_id'] . $this->increment_id_suffix; // add suffix
				if (!$already_obtain_headers)
				{
					$colHeaders = $this->writeColHeaders($row);
					$already_obtain_headers = true;
				}
				$data[] = $row;
			}
		}

		// insert into destination table
		foreach($data as $key => $values)
		{
			// insert into destination table
			$insertQuery = "INSERT INTO sales_flat_invoice_grid ({$colHeaders}) VALUES ";
			$insertQuery .= '(' . $this->getColValues($values) . ')';

			$result = mysqli_query($destlink, $insertQuery);
			if (!$result) 
			    die('Error insert: ' . mysqli_error($destlink));
		}

		echo "<br />==========================================================<br />";
		echo "FINISH COPYING INVOICE GRID FROM PRODUCTION<br /><br />";
	}

	private function copy_sales_flat_invoice_item($prodlink, $destlink)
	{
		echo "BEGIN COPYING INVOICE ITEM FROM PRODUCTION<br />";
		echo "==========================================================<br /><br />";

		if (count($this->invoice_entity_id_mapper) < 1)
			return;

		$data = array();
		$result = mysqli_query($prodlink, "SELECT * FROM sales_flat_invoice_item WHERE parent_id IN ({$this->convertKeysToCommas($this->invoice_entity_id_mapper)}) ORDER BY entity_id");
		if (!$result) {
		    die('Invalid query: ' . mysqli_error($prodlink));
		}

		$already_obtain_headers = false;
		$colHeaders = '';

		if (count($result) > 0)
		{
			while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC) ) {
				$row['parent_id'] = $this->invoice_entity_id_mapper[$row['parent_id']]; // replace the parent ID from the mapper
				$row['order_item_id'] = $this->sales_item_id_mapper[$row['order_item_id']]; // replace the order item ID from the mapper
				if (!$already_obtain_headers)
				{
					$colHeaders = $this->writeColHeaders($row, 'sales_flat_invoice_item');
					$already_obtain_headers = true;
				}
				$data[] = $row;
			}
		}

		$already_get_first_item_id = false;
		$first_item_id = 0;

		// insert into destination table
		foreach($data as $key => $values)
		{
			// insert into destination table
			$insertQuery = "INSERT INTO sales_flat_invoice_item ({$colHeaders}) VALUES ";
			$insertQuery .= '(' . $this->getColValues($values, 'sales_flat_invoice_item') . ')';

			$result = mysqli_query($destlink, $insertQuery);
			if (!$result) 
			    die('Error insert: ' . mysqli_error($destlink));
		}

		echo "<br />==========================================================<br />";
		echo "FINISH COPYING INVOICE ITEM FROM PRODUCTION<br /><br />";
	}

	/* COPY TO TABLE MESSAGE TO BE EXPORTED TO NETSUITE */
	private function copy_table_message($destlink)
	{
		echo "BEGIN COPYING TO TABLE MESSAGE<br />";
		echo "==========================================================<br /><br />";

		// insert into destination table
		// Sales Order first
		foreach($this->sales_entity_id_mapper as $key => $value)
		{
			$queue_id = 1;
			$body = 'order_place|' . $value;
			$md5 = md5($body);
			$created = time();
			$priority = 0;
			$processing = 0;

			// insert into destination table
			$insertQuery = "INSERT INTO message (queue_id, body, md5, created, priority, processing) VALUES ";
			$insertQuery .= "($queue_id, '$body', '$md5', '$created', $priority, $processing)";

			$result = mysqli_query($destlink, $insertQuery);
			if (!$result) 
			    die('Error insert: ' . mysqli_error($destlink));
		}

		// insert into destination table
		// Invoice later
		foreach($this->invoice_entity_id_mapper as $key => $value)
		{
			$queue_id = 1;
			$body = 'invoice_save|' . $value;
			$md5 = md5($body);
			$created = time();
			$priority = 0;
			$processing = 0;

			// insert into destination table
			$insertQuery = "INSERT INTO message (queue_id, body, md5, created, priority, processing) VALUES ";
			$insertQuery .= "($queue_id, '$body', '$md5', '$created', $priority, $processing)";

			$result = mysqli_query($destlink, $insertQuery);
			if (!$result) 
			    die('Error insert: ' . mysqli_error($destlink));
		}

		echo "<br />==========================================================<br />";
		echo "FINISH COPYING TO TABLE MESSAGE<br /><br />";
	}

	// get billing address id and shipping address id based on sales order ID
	private function get_address_type_ids($order_id, $destlink)
	{
		$data = array();
		$result = mysqli_query($destlink, "SELECT billing_address_id, shipping_address_id FROM sales_flat_order WHERE entity_id = $order_id");
		if (!$result) {
		    die('Invalid query: ' . mysqli_error($destlink));
		}

		if (count($result) > 0)
		{
			while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC) ) {
				$data['billing_address_id'] = $row['billing_address_id'];
				$data['shipping_address_id'] = $row['shipping_address_id'];
			}
		}

		return $data;
	}

	/* OTHER FUNCTIONS */
	private function convertKeysToCommas($data)
	{
		$result = '';

		foreach($data as $key => $value)
			$result .= $key . ',';

		if (strlen($result) > 0)
			$result = substr($result, 0, strlen($result) - 1);

		return $result;
	}

	private function convertValuesToCommas($data)
	{
		$result = '';

		foreach($data as $key => $value)
			$result .= $value . ',';

		if (strlen($result) > 0)
			$result = substr($result, 0, strlen($result) - 1);

		return $result;
	}

	private function writeColHeaders($row, $entity = null)
	{
		$result = '';

		foreach($row as $key => $value)
		{
			if (!is_null($entity) && in_array($key, $this->fields_excepted[$entity]))
				continue;
			
			$result .= $key . ',';
		}

		if (strlen($result) > 0)
			$result = substr($result, 0, strlen($result) - 1);

		return $result;
	}

	private function getColValues($values, $entity = null)
	{
		$result = '';

		foreach($values as $key => $value)
		{
			if (!is_null($entity) && in_array($key, $this->fields_excepted[$entity]))
				continue;

			if (is_null($values[$key]))
				$result .= 'NULL,';
			else
				$result .= "'".addslashes($values[$key])."'" . ',';
		}

		if (strlen($result) > 0)
			$result = substr($result, 0, strlen($result) - 1);

		return $result;
	}
}
?>