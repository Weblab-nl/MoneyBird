<?php

namespace Weblab\MoneyBird\Repositories;

/**
 * Class Contacts
 * @author Weblab.nl - Eelco Verbeek
 */
class Contacts extends AbstractRepository {

    const MODEL = 'contact';

    /**
     * Search for contacts on: name, id, address, zipcode, email, etc, etc
     *
     * @param $query
     * @return array|null
     * @throws \Exception
     */
    public function search($query) {
        // Get the class for the model
        $class = $this->getModelClass();

        // Build path
        $path = '/' . $class::ENDPOINT;

        // Do API call
        $result = $this->api->get($path, ['query' => $query]);

        // If status not 200 return null
        if ($result->getStatus() !== 200) {
            return [];
        }

        // Initialize contacts array
        $contacts = [];

        // Loop API results
        foreach ($result->getResult() as $contact) {
            // Store contacts in array
            $contacts[] = new $class($this->api, $contact, true);
        }

        // Return found contacts
        return $contacts;
    }

}
