<?php

use PitouFW\Core\Controller;
use PitouFW\Core\Data;

if(ENV_NAME === "prod"){
    Controller::http404NotFound();
    Data::get()->add('name', NAME);
    Data::get()->add('version', ['ref' => DEPLOYED_REF, 'hash' => substr(DEPLOYED_COMMIT, 0, 5)]);
    Controller::renderApiSuccess();
}else{
    Data::get()->add('name', NAME);
    Data::get()->add('version', ['ref' => DEPLOYED_REF, 'hash' => DEPLOYED_COMMIT]);
    Controller::renderApiSuccess();
}
