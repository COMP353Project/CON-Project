<?php

namespace Web;

use Http\Request;
use Utils\DB\DB;
use Utils\DB\DBConn;

class PageRenderer {

    private const TEMPLATES = [
        "navbar" => [
            "html" => "static/html/navbar.html"
        ],
        "login" => [
            "html" => "static/html/index.html"
        ],
        "homepage" => [
            "html" => "static/html/public.html"
        ],
        "head" => [
            "html" => "static/html/head.html"]
        ,
        "aboutPage" => [
            "html" => "static/html/about.html",
            "css" => [
                "<script src=\"https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js\"></script>"
            ]
        ],
        "profilePage" => [
            "html" => "static/html/profile.html",
            "css" => [
                "<link rel=\"stylesheet\" href=\"/css/profile.css\">"
            ]
        ],
        "administerPage" => [
            "html" => "static/html/administer.html",
            "css" => [
                "<link rel=\"stylesheet\" href=\"/css/administer.css\">"
            ],
            "extraStyle" => [
                "<style>
                  .bd-placeholder-img {
                    font-size: 1.125rem;
                    text-anchor: middle;
                    -webkit-user-select: none;
                    -moz-user-select: none;
                    -ms-user-select: none;
                    user-select: none;
                  }
            
                  @media (min-width: 768px) {
                    .bd-placeholder-img-lg {
                      font-size: 3.5rem;
                    }
                  }
                </style>"
            ]
        ],
        "groupPage" => [
            "html" => "static/html/group.html",
            "css" => [
                "<link rel=\"stylesheet\" href=\"/css/group.css\">"
            ]
        ],
        "emailPage" => [
            "html" => "static/html/email.html",
            "css" => [
                "<link rel=\"stylesheet\" href=\"/css/email.css\">"
            ]
        ],
    ];

    private $targetPage;
    private $requestContext;
    private $requestArgs;
    private $targetTemplate;
    /* @var $dbConn DBConn */
    private $dbConn;

    public function __construct($pageName, Request $requestContext, $args) {
        $this->targetPage = $pageName;
        $this->requestContext = $requestContext;
        $this->requestArgs = $args;
        $this->dbConn = DB::getInstance()->getConnection();
    }

    static function renderHomePage(Request $request, $args) {
        // render the home page
        $renderer = new PageRenderer("homepage", $request, $args);
        $renderer->renderPage();
        $renderer->finish();
    }


    static function renderLogIn(Request $request, $args) {
        $renderer = new PageRenderer("login", $request, $args);
        $renderer->renderPage();
        $renderer->finish();
    }

    static function renderPageForWeb(Request $request, $args, $pageName) {
        $renderer = new PageRenderer($pageName, $request, $args);
        $renderer->renderPage();
        $renderer->finish();
    }

    static function renderEmailPage(Request $request, $args) {
        $renderer = new PageRenderer("email", $request, $args);
        $renderer->renderPage();
        $renderer->finish();
    }

    private function renderPage() {
        $this->targetTemplate = $this->renderTemplate($this->targetPage);
    }

    private function renderTemplate($templateName): string {
        $template = $this->readTemplate($templateName);
        return $this->buildTemplate($template);
    }

    private function readTemplate($templateName): string {
        $fileName = __DIR__ . '/../../' . self::TEMPLATES[$templateName]['html'];
        $openTemplate = fopen($fileName, 'r') or die("Webpage unavailable");
        $template = fread($openTemplate, filesize($fileName));
        fclose($openTemplate);
        return $template;
    }

    private function buildTemplate($template): string {
        $templateMatches = [];
        $functionMatches = [];
        while (
            preg_match("/{%T ([a-zA-Z]+) %T}/", $template, $templateMatches, PREG_OFFSET_CAPTURE) ||
            preg_match("/{%F ([a-zA-Z]+) %F}/", $template, $functionMatches, PREG_OFFSET_CAPTURE)
        ) {
            if (sizeof($templateMatches) > 0) {
                // found a template
                // recursively build the template
                $builtTemplate = $this->renderTemplate($templateMatches[1][0]);
                // insert the sub-template into the template
                $template = substr_replace($template, $builtTemplate, $templateMatches[0][1], strlen($templateMatches[0][0]));
            } else {
                $method = $functionMatches[1][0];
                $functionResult = $this->$method();
                $template = substr_replace($template, $functionResult, $functionMatches[0][1], strlen($functionMatches[0][0]));
            }
            // reset
            $templateMatches = [];
            $functionMatches = [];
        }

        return $template;
    }

    private function finish() {
        header('Content-Type: text/html');
        echo $this->targetTemplate;
    }

    private function chooseOrderOfJS(): string {
        $defaultFirst = "<script src=\"https://code.jquery.com/jquery-3.5.1.slim.min.js\" integrity=\"sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj\" crossorigin=\"anonymous\"></script>";
        $defaultSecond = "<script src=\"https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js\" integrity=\"sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx\" crossorigin=\"anonymous\"></script>";

        return implode("\r\n    ", (($this->targetPage != 'administerPage') ? [$defaultFirst, $defaultSecond] : [$defaultSecond, $defaultFirst]));
    }

    private function extraStyle() {
        if (in_array('extraStyle', array_keys(self::TEMPLATES[$this->targetPage]))) {
            return implode("\r\n    ", self::TEMPLATES[$this->targetPage]['extraStyle']);
        }
        return "";
    }

    private function aboutLink() {
        return ($this->targetPage == 'homepage') ?
            "location.href='#about'" :
            "location.href='/ataglance'";
    }

    private function extraCss(): string {
        if (in_array('css', array_keys(self::TEMPLATES[$this->targetPage]))) {
            return implode("\r\n    ", self::TEMPLATES[$this->targetPage]['css']);
        }
        return "";
    }

    private function loginOrLogout() {
        if (!isset($_SESSION['userId'])) {
            [$route, $label] = ["/login", "LOG IN"];
            $lastItem = <<<EOD
<li class="nav-item">
                    <a class="nav-link" href="$route">$label</a>
                </li>
EOD;
        } else {
            // get user info
            $sql = <<<EOD
select 
       u.firstname, 
       u.lastname, 
       r.name 
from users u
join user_roles ur
on u.id = ur.userid
join roles r 
on r.id = ur.roleid
where u.id = :user_id
EOD;

            $userInfo = $this->dbConn->queryWithValues($sql, [":user_id" => $_SESSION['userId']]);
            $userName = $userInfo[0]['firstName'] . " " . $userInfo[0]['lastName'];
            $superUserItem = "";
            // administrators get extra button in user dropdown
            if ($userInfo[0]['name'] == "superuser") {
                $superUserItem = <<<EOD
<a class="dropdown-item" href="/administer"><i class="fa fa-database"></i>&nbsp;&nbsp;Administer</a>
                        
EOD;
            }
            // create item
            $lastItem = <<<EOD
<li class="nav-item dropdown">
                    <a class="btn-floating btn-lg black dropdown-toggle" type="button" id="dropdown-menu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-user-circle"></i></a>
                    <div class="dropdown-menu dropdown-primary dropdown-menu-right">
                        <a class="dropdown-item">$userName</a>
                        <a class="dropdown-item" href="/profile"><i class="fa fa-user-secret"></i>&nbsp;&nbsp;Your Page</a>
                        $superUserItem<a class="dropdown-item" href="/email"><i class="fa fa-envelope"></i>&nbsp;&nbsp;Check mail</a>
                        <a class="dropdown-item" href="/logout"><i class="fa fa-user-times"></i>&nbsp;&nbsp;Log Out</a>
                    </div>
                </li>
EOD;

        }
        return $lastItem;
    }

    private function getUserName() : string {
        $sql = "select firstname, lastname from users where id = :userid";
        $userInfo = $this->dbConn->queryWithValues($sql, [":userid" => $_SESSION['userId']]);
        return $userInfo[0]['firstName'] . " " . $userInfo[0]['lastName'];
    }

    private function getUserInfoForProfile() : string {
        $item = "%FIELD - %VALUE";
        $sql = <<<EOD
with association_info as (
	select ur.userid,
	assoc.name as assoc_name,
	r.name as r_name
	from user_roles ur
	join condo_association assoc on ur.associationid = assoc.id
	join roles r on ur.roleid = r.id
	where ur.userid = :user_id
)
select
u.createdon,
group_concat(ai.assoc_name) as associations,
group_concat(ai.r_name) as roles,
(select count(*) from condo_unit cu where cu.ownerid = :user_id) as numcondos
from users u
join association_info ai on u.id = ai.userid
where u.id = :user_id
group by u.id
EOD;
        $userInfo = $this->dbConn->queryWithValues($sql, [":user_id" => $_SESSION['userId']]);

        $userHtml = [];
        foreach ($userInfo[0] as $field => $value) {
            if (in_array($field, ['associations', 'roles'])) {
                $value = explode(",", $value);
                if ($field == 'roles') {
                    $value = array_unique($value);
                }
                $value = implode(", ", $value);
            }
            $newHtml = str_replace("%FIELD", $field, $item);
            $userHtml[] = str_replace("%VALUE", $value, $newHtml);
        }
        return implode("<br>", $userHtml);
    }

    private function getUserGroups(): string {
        $sql = <<<EOD
select cg.id, cg.name 
from group_membership gm
join con_group cg on gm.groupid = cg.id 
where gm.userid = :user_id
EOD;
        $userGroups = $this->dbConn->queryWithValues($sql, [":user_id" => $_SESSION['userId']]);
        $html = <<<EOD
<table class="table table-striped" id="group-name-table">
    <thead>
        <th scope="col">
            <a onclick="createGroupPopUp()">
                <i class="fa fa-edit"></i>
            </a>
            <div class="tooltip">
                <span class="tooltiptext">Create group</span>
            </div>
        </th>
        <th scope="col">Your Groups</th>
    </thead>
    <tbody>
        %TABLE%
    </tbody>
</table>
EOD;
        $groupBase = "<tr><th scope=\"row\">%ID%</th><th><a %REF%>%GROUP_NAME%</a></th></tr>";
        $counter = 1;
        if (sizeof($userGroups) == 0) {

            $groupHtml = str_replace("%GROUP_NAME%", "No groups to display", $groupBase);
            $groupHtml = str_replace("%ID%", $counter, $groupHtml);
            $groupHtml = str_replace("%REF%", "", $groupHtml);
        } else {
            $groupHtml = "";
            foreach($userGroups as $userGroup) {
                $interm = str_replace("%GROUP_NAME%", $userGroup['name'], $groupBase);
                $interm = str_replace("%ID%", $counter, $interm);
                $groupHtml .= str_replace("%REF%", "href=\"/groups/{$userGroup['id']}\"", $interm);
                $counter++;
            }
        }

        return str_replace("%TABLE%", $groupHtml, $html);
    }

    private function getGroupName() : string {
        $res = $this->dbConn->queryWithValues(
            "select name from con_group where id = :group_id",
            [":group_id" => $this->requestArgs['id']]
        );

        if (sizeof($res) == 0) {
            return "Invalid GROUP ID";
        } else {
            return $res[0]['name'];
        }
    }

    private function getGroupInfoForDisplay(): string {
        $item = "%FIELD - %VALUE";
        $sql = <<<EOD
with membership as (
	select roleid, count(*) as num_users
	from group_membership
	where groupid = :groupid
	group by roleid
)
select
description,
createdon,
coalesce((select num_users from membership where roleid = 1),0) as nummembers,
coalesce((select num_users from membership where roleid = 2),0) as numadmins
from con_group
where id = :groupid
EOD;
        $res = $this->dbConn->queryWithValues($sql, [":groupid" => $this->requestArgs['id']]);
        $groupHtml = [];
        foreach ($res[0] as $field => $value) {
            $newHtml = str_replace("%FIELD", $field, $item);
            $groupHtml[] = str_replace("%VALUE", $value, $newHtml);
        }

        return implode("<br>", $groupHtml);
    }

    private function getGroupAdmins(): string {
        return $this->getGroupMembersByRole("ADMINS", 2);
    }

    private function getGroupMembers(): string {
        return $this->getGroupMembersByRole("MEMBERS", 1);
    }

    private function getGroupMembersByRole($roleName, $roleId): string {
        $sql = <<<EOD
select u.firstname, u.lastname
from users u 
join group_membership g on u.id = g.userid
where g.groupid = :groupid and g.roleid = :roleid
EOD;
        $res = $this->dbConn->queryWithValues($sql, [
            ":groupid" => $this->requestArgs['id'],
            ":roleid" => $roleId
        ]);

        $listHtml = "<ul class=\"list-group\">" .
            "    <li class=\"list-group-item d-flex justify-content-between align-items-center disabled\">
                 {$roleName}
                 <span><i class=\"fa fa-cogs\"></i>&nbsp;&nbsp;</span>
                 </li>" .
            "    %LISTITEMS%" .
            "</ul>";
        if (sizeof($res) > 0) {
            $listItems = [];
            foreach ($res as $member) {
                $listItems[] = "    <li class=\"list-group-item d-flex justify-content-between align-items-center\">{$member['firstName']} {$member['lastName']}</li>";
            }
            $listItems = implode("", $listItems);
        } else {
            $listItems = "    <li class=\"list-group-item d-flex justify-content-between align-items-center\">No " . strtolower($roleName) . "</li>";
        }

        return str_replace("%LISTITEMS%", $listItems, $listHtml);
    }

    private function addNewMessage()
    {
        //ISSET
        //Create vars
        //Do POST
        //INSERT
        $sql = "select cg.id, cg.name 
                from group_membership gm
                join con_group cg on gm.groupid = cg.id 
                where gm.userid = :user_id";

        $userGroups = $this->dbConn->queryWithValues($sql, [":user_id" => $_SESSION['userId']]);
    }

}

