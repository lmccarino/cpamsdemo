<?php
$mymenu = [
"Dashboard"=> ["url"=>"index.html","display"=>true,"image"=>"images/users.jpg","icon"=>"fa-solid fa-chart-line"],
"Assessment"=>[
				"Verify Patient"=>["url"=>"verifypatientView.html","display"=>true,"image"=>"images/safebox.png","icon"=>"fas fa-heart-circle-check"],
				"Verify Requestor"=>["url"=>"verifyrequestorView.html","display"=>true,"image"=>"images/safebox.png","icon"=>"fas fa-users"],
				"icon"=>"fas fa-heart-circle-check"
			  ],
"Encoding"=>[
				"Private"=>["url"=>"privateView.html","display"=>true,"image"=>"images/users.jpg","icon"=>"far fa-face-grin-wide"],
				"Government"=>["url"=>"governmentView.html","display"=>true,"image"=>"images/users.jpg","icon"=>"far fa-face-grin"],
				"Pending"=>["url"=>"pendingView.html","display"=>true,"image"=>"images/users.jpg","icon"=>"far fa-circle-pause"],
				"icon"=>"far fa-pen-to-square"
			],
"Override"=>[
				"For Approval"=>["url"=>"overrideView.html","display"=>true,"image"=>"images/safebox.png","icon"=>"fa fa-thumbs-up"],
				"Approved Request"=>["url"=>"approvedView.html","display"=>true,"image"=>"images/safebox.png","icon"=>"fas fa-user-check"],
				"Patient Correction"=>["url"=>"patientCorrectionView.html","display"=>true,"image"=>"images/safebox.png","icon"=>"fas fa-user-pen"],
				"RAF Correction"=>["url"=>"rafCorrectionView.html","display"=>true,"image"=>"images/safebox.png","icon"=>"far fa-file-alt"],
				//"RAF Corrections" =>["url"=>"rafView.html","display"=>true,"image"=>"images/safebox.png","icon"=>"far fa-pen-to-square"],
				"icon"=>"far fa-thumbs-up"
			],
"Inquiry"=> ["url"=>"inquiry.html","display"=>true,"image"=>"images/users.jpg","icon"=>"fa-solid fa-chart-line"],
"Transmittals"=> ["url"=>"transmittals.html","display"=>true,"image"=>"images/users.jpg","icon"=>"fa-solid fa-file-export"],
"Funds"=>[
	"Replenish Account"=>["url"=>"replenishView.html","display"=>true,"image"=>"images/users.jpg","icon"=>"fas fa-credit-card"],
	"Adjust Account"=>["url"=>"adjustView.html","display"=>true,"image"=>"images/users.jpg","icon"=>"fas fa-money-bill-1"],
	"Monitor Fund Balance"=>["url"=>"monitortView.html","display"=>true,"image"=>"images/users.jpg","icon"=>"fas fa-chart-bar"],
	"icon"=>"fas fa-money-bill-1"
	],

"Reports"=>[
	"Accomplishment Report"=>["url"=>"accomplishmentView.html","display"=>true,"image"=>"images/certificate.png","icon"=>"fas fa-list"],
	"Dialysis Related Reports"=>["url"=>"masterlistdialysisView.html","display"=>true,"image"=>"images/certificate.png","icon"=>"fas fa-users"],
	"Approved Assistance by Provider"=>["url"=>"masterlistViewProvider.html","display"=>true,"image"=>"images/certificate.png","icon"=>"fas fa-th-list"],
	"Clients Catered by Barangay"=>["url"=>"clientsbybrgy.html","display"=>true,"image"=>"images/certificate.png","icon"=>"fas fa-table"],
	"Summary of Clients Served"=>["url"=>"clientsServedSummary.html","display"=>true,"image"=>"images/certificate.png","icon"=>"fas fa-file-alt"],
	"icon"=>"far fa-file",
	"Approved Assistance by Beneficiary"=>["url"=>"masterlistbybeneficiaryView.html","display"=>true,"image"=>"images/certificate.png","icon"=>"fas fa-list"],
	"Masterlist of Override Assistance"=>["url"=>"overrideAssistanceView.html","display"=>true,"image"=>"images/certificate.png","icon"=>"fas fa-undo"],
	"Masterlist of Cancelled Assistance"=>["url"=>"cancelledAssistanceView.html","display"=>true,"image"=>"images/certificate.png","icon"=>"fas fa-trash-alt"],
	"User Accomplishment Reports"=>["url"=>"userAccomplishmentView.html","display"=>true,"image"=>"images/certificate.png","icon"=>"fas fa-file-pdf"],

	"icon"=>"far fa-file"
	],
"System Support"=>[
	"Raise Issue"=>["url"=>"raiseissueView.html","display"=>true,"image"=>"images/certificate.png","icon"=>"far fa-hand"],
	"Receive Issue"=>["url"=>"receiveissueView.html","display"=>true,"image"=>"images/certificate.png","icon"=>"far fa-handshake"],
	"Messages"=>["url"=>"messagesView.html","display"=>true,"icon"=>"far fa-handshake"],
	"Send Messages"=>["url"=>"sendmessagesView.html","display"=>true,"icon"=>"far fa-handshake"],
	"icon"=>"fa fa-handshake"

	],
	
"Administration"=>[
	"Users"=>["url"=>"usersView.html","display"=>true,"image"=>"images/users.jpg","icon"=>"fas fa-clipboard-user"],
	"User Profiles"=>["url"=>"usersView1.html","display"=>true,"image"=>"images/users.jpg","icon"=>"fas fa-id-card"],
	"Roles"=>["url"=>"rolesView.html","display"=>true,"image"=>"images/users.jpg","icon"=>"fas fa-address-book"],
	"Verify Device"=>["url"=>"verifydeviceView.html","display"=>true,"image"=>"images/users.jpg","icon"=>"fas fa-mobile-retro"],
		"Audit Logs"=>["url"=>"auditlogsView.html","display"=>true,"image"=>"images/fees.png","icon"=>"fas fa-folder-open"],
	"Office"=>["url"=>"officeView.html","display"=>true,"image"=>"images/users.jpg","icon"=>"fas fa-people-roof"],
	"Provider Assistance"=>["url"=>"provassistView.html","display"=>true,"image"=>"images/users.jpg","icon"=>"fas fa-hand-holding-heart"],
	"Assist Rate"=>["url"=>"assistrateView.html","display"=>true,"image"=>"images/users.jpg","icon"=>"fas fa-hand-holding-heart"],
	"icon"=>"fa fa-bars-progress"
	
	
	],
"Providers"=>[
	"Guarantee Letter"=>["url"=>"providerGLview.html","display"=>true,"image"=>"images/users.jpg","icon"=>"fas fa-hand-holding-heart"],
	"Statement of Account"=>["url"=>"statementofaccount.html","display"=>true,"image"=>"images/users.jpg","icon"=>"fas fa-file-alt"],
	"City Accounting Office"=>["url"=>"cswdoView.html","display"=>true,"image"=>"images/users.jpg","icon"=>"fa-solid fa-file-circle-check"],
	"icon"=>"fa fa-hand-holding-heart"
	]
];

?>