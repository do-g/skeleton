<?php

Core_Router::i()->add('/', [
	'controller' => 'test',
	'action' => 'test',
	'token' => 'test1234',
], [
	'subdomain' => 'test',
]);
Core_Router::i()->add('/google', [
	'redirect' => 'https://www.google.com',
]);