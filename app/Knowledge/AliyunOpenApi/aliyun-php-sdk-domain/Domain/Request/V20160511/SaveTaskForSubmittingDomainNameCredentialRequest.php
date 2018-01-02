<?php
/*
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 */
namespace Domain\Request\V20160511;

class SaveTaskForSubmittingDomainNameCredentialRequest extends \RpcAcsRequest
{
	function  __construct()
	{
		parent::__construct("Domain", "2016-05-11", "SaveTaskForSubmittingDomainNameCredential");
		$this->setMethod("POST");
	}

	private  $credentialNo;

	private  $saleId;

	private  $credential;

	private  $userClientIp;

	private  $domainName;

	private  $lang;

	public function getCredentialNo() {
		return $this->credentialNo;
	}

	public function setCredentialNo($credentialNo) {
		$this->credentialNo = $credentialNo;
		$this->queryParameters["CredentialNo"]=$credentialNo;
	}

	public function getSaleId() {
		return $this->saleId;
	}

	public function setSaleId($saleId) {
		$this->saleId = $saleId;
		$this->queryParameters["SaleId"]=$saleId;
	}

	public function getCredential() {
		return $this->credential;
	}

	public function setCredential($credential) {
		$this->credential = $credential;
		$this->queryParameters["Credential"]=$credential;
	}

	public function getUserClientIp() {
		return $this->userClientIp;
	}

	public function setUserClientIp($userClientIp) {
		$this->userClientIp = $userClientIp;
		$this->queryParameters["UserClientIp"]=$userClientIp;
	}

	public function getDomainName() {
		return $this->domainName;
	}

	public function setDomainName($domainName) {
		$this->domainName = $domainName;
		$this->queryParameters["DomainName"]=$domainName;
	}

	public function getLang() {
		return $this->lang;
	}

	public function setLang($lang) {
		$this->lang = $lang;
		$this->queryParameters["Lang"]=$lang;
	}
	
}