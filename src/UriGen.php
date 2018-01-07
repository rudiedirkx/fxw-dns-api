<?php

namespace rdx\fxwdns;

class UriGen {

	public function editDomainDns( Domain $domain ) {
		$show = $domain->direct ? 'showDirect' : 'show';
		return "/dns/$show/{$domain->id}";
	}

	public function insertDnsRecord( Domain $domain ) {
		return "/dns/add";
	}

	public function editDnsRecord( Domain $domain, DnsRecord $record ) {
		$editShow = $domain->direct ? 'editShowDirect' : 'editShow';
		return "/dns/$editShow/{$record->id}/{$domain->id}";
	}

	public function updateDnsRecord( Domain $domain, DnsRecord $record ) {
		$edit = $domain->direct ? 'editDirect' : 'edit';
		return "/dns/$edit";
	}

	public function preDeleteDnsRecord( Domain $domain, DnsRecord $record ) {
		$deleteShow = $domain->direct ? 'deleteShowDirect' : 'deleteShow';
		return "/dns/$deleteShow/{$record->id}/{$domain->id}";
	}

	public function deleteDnsRecord( Domain $domain, DnsRecord $record ) {
		$delete = $domain->direct ? 'deleteDirect' : 'delete';
		return "/dns/$delete";
	}

}
