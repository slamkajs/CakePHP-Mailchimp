<?php

App::uses('DataSource', 'Model/Datasource');

/**
 * Datasource for Mailchimp
 *
 * Used for saving, selecting and deleting Subscribers
 *
 * Example URL:
 * http://api.mailchimp.com/1.2/?method=listSubscribe&apikey=<apikey>&id=<list id>&email_address=<email_address>&merge_vars[FNAME]=Firstname&merge_vars[LNAME]=Lastname&merge_vars[INTERESTS]=Dogs,Cats,Shoes&output=json
 *
 * Use the $_schema in the model to supply custom values. The names must coincide with the values you created in mailchimp
 */
class MailchimpSubscriberSource extends DataSource {

	public $settings = array();

	/**
	 * Construct our Datasource Class
	 * @param <type> $config
	 */
	public function __construct($config = array()) {
		$config = array_merge((array)Configure::read('Mailchimp'), $config);
		//set ApiKey, default list Id and baseUrl
		$this->settings = $config;

		App::import('Vendor', 'Mailchimp.mailchimp/MCAPI.class');
		$this->Mailchimp = new MCAPI(Configure::read('Mailchimp.apiKey'));

		parent::__construct($config);
	}

	/**
	 * ListSources()
	 *
	 * Required by CakePHP
	 * @return <type>
	 */
	public function listSources() {
		return array('Mailchimp');
	}

	/**
	 * describe()
	 *
	 * Required by CakePHP
	 * @param <type> $model
	 * @return <type>
	 */
	public function describe($model) {
		return $this->_schema['Mailchimp'];
	}

	/**
	 * Find a subscriber
	 *
	 * @param object $model
	 * @param array $queryData
	 * @return array
	 */
	public function read(Model $model, $queryData = array()) {
		//$url = $this->buildUrl('listMemberInfo', $queryData['conditions']['email']);
		//$response = json_decode($this->connection->get($url), true);
		$response = $this->Mailchimp->listMemberInfo($this->settings['listId'], $queryData['conditions']['emailaddress']);

		return $response;
	}


	/**
	 * Add a subscriber to the list
	 *
	 * @param object $model
	 * @param array $fields
	 * @param array $values
	 * @return boolean
	 */
	public function create(Model $model, $fields = array(), $values = array()) {
		$data = array_combine($fields, $values);
		$email = $data['email'];
		unset($data['email']);

		$response = $this->Mailchimp->listSubscribe($this->settings['listId'], $email);
		return $response;
	}

	/**
	 * Updates a subscriber on the list
	 * //TODO: test
	 *
	 * @param object $model
	 * @param array $fields
	 * @param array $values
	 * @return boolean
	 */
	public function update(Model $Model, $fields = array(), $values = array()) {
		return $this->create($Model, $fields, $values);
	}

	/**
	 * Delete a subscriber from the list
	 *
	 * @param object $model
	 * @param string $id either email or id of the subscriber
	 * @return boolean
	 */
	public function delete(Model $model, $id = null) {
		//we can use $id instead of $email here, this is allowed as per Mailchimp's API docs,
		//$url = $this->buildUrl('listUnsubscribe', $model->id);
		$response = $this->Mailchimp->listUnsubscribe($this->settings['defaultListId'], $model->id);
		return $response;
	}

}
