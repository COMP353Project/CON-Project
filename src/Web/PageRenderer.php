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
        ]
    ];

    private $targetPage;
    private $requestContext;
    private $requestArgs;
    private $targetTemplate;

    public function __construct($pageName, Request $requestContext, $args) {
        $this->targetPage = $pageName;
        $this->requestContext = $requestContext;
        $this->requestArgs = $args;
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

            /* @var $dbConn DBConn */
            $dbConn = DB::getInstance()->getConnection();
            $userInfo = $dbConn->queryWithValues(
                $sql,
                [":user_id" => $_SESSION['userId']]
            );
            $userName = $userInfo[0]['firstName'] . " " . $userInfo[0]['lastName'];
            $superUserItem = "";
            // administrators get extra button in user dropdown
            if ($userInfo[0]['name'] == "superuser") {
                $superUserItem = <<<EOD
<a class="dropdown-item" href="#"><i class="fa fa-database"></i>&nbsp;&nbsp;Administer</a>
                        
EOD;
            }
            // create item
            $lastItem = <<<EOD
<li class="nav-item dropdown">
                    <a class="btn-floating btn-lg black dropdown-toggle" type="button" id="dropdown-menu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-user-circle"></i></a>
                    <div class="dropdown-menu dropdown-primary dropdown-menu-right">
                        <a class="dropdown-item">$userName</a>
                        <a class="dropdown-item" href="/profile"><i class="fa fa-user-secret"></i>&nbsp;&nbsp;Your Page</a>
                        $superUserItem<a class="dropdown-item" href="#"><i class="fa fa-envelope"></i>&nbsp;&nbsp;Check mail</a>
                        <a class="dropdown-item" href="/logout"><i class="fa fa-user-times"></i>&nbsp;&nbsp;Log Out</a>
                    </div>
                </li>
EOD;

        }
        return $lastItem;
    }

    private function getUserName() : string {
        $sql = "select firstname, lastname from users where id = :userid";
        $dbConn = DB::getInstance()->getConnection();
        $userInfo = $dbConn->queryWithValues($sql, [":userid" => $_SESSION['userId']]);
        return $userInfo[0]['firstName'] . " " . $userInfo[0]['lastName'];
    }
}

