<?php


namespace Web\DbAPI;

include_once(__DIR__ . "/../Utils/DB/DB.php");
include_once(__DIR__ . "/../Utils/DB/DBConn.php");


use Utils\DB\DB;
use Utils\DB\DBConn;

/* =====================================================================
 *
 * FUNCTIONS TO INTERACT WITH DB
 *
 * =====================================================================
 */

function getUsersFromDB($userIds) {
    $userIds = (is_array($userIds)) ? $userIds : [$userIds];
    $idCounter = 0;
    $parametrizedIds = [];
    $userIdParams = implode(
        ", ",
        array_map(
            function($id) use (&$idCounter, &$parametrizedIds, $userIds) {
                $idCounter++;
                $idx = ":user_id_" . $idCounter;
                $parametrizedIds[$idx] = $userIds[$idCounter - 1];
                return $idx;
            },
            $userIds
        )
    );
    $whereClause = (sizeof($userIds) == 0) ? "" : "WHERE id IN ({$userIdParams})";
    $usersSQL = "select id, firstname, lastname, isactive, createdon from users {$whereClause};";
    /* @var $dbConn DBConn */
    $dbConn = DB::getInstance()->getConnection();
    $res = $dbConn->queryWithValues($usersSQL, $parametrizedIds);

    return $res;
}

function getPosts($userId, $groupId, $associationId) {
    // if user is specified, request is coming from user profile page
    //  --> get all posts the user can see
    // if group is specified, request is coming from group page
    //  --> get all posts in a group
    // if association is specified, request is coming from association page
    //  --> get all posts by users in an association
    if (!is_null($userId)) {
        $whereSql = "(
	        p.group_id in (select distinct groupid from group_membership where userid = :userid)
	        or
	        ur.associationid in (select distinct associationid from user_roles where userid = :userid)
        )";
        $params = [":userid" => $userId];
    } elseif (!is_null($groupId)) {
        $whereSql = "p.group_id = :group_id";
        $params = [":group_id" => $groupId];
    } else {
        $whereSql = "ur.associationid = :associationid";
        $params = [":asociationid" => $associationId];
    }
    $sql = <<<EOD
select 
p.post_id, p.user_id, 
u.firstname, u.lastname, g.name as groupname, 
p.contents, p.is_commentable, p.tstamp,
(select count(*) as numcomments from Comments where post_id = p.post_id) as numcomments
from Posts p
join users u on p.user_id = u.id
left join con_group g on p.group_id = g.id
join user_roles ur on p.user_id = ur.userid
where {$whereSql}
order by p.tstamp
EOD;

    /* @var $dbConn DBConn */
    $dbConn = DB::getInstance()->getConnection();
    $res = $dbConn->queryWithValues($sql, $params);
    return $res;
}


function getPostFromDB($postId) {
    // TODO check for permissions
    $sql = <<<EOD
select p.post_id, p.contents, p.is_commentable, p.tstamp, (select count(*) as numcomments from Comments where post_id = :postid) as numcomments
from Posts p
where p.post_id = :postid;
EOD;

    /* @var $dbConn DBConn */
    $dbConn = DB::getInstance()->getConnection();
    $res = $dbConn->queryWithValues($sql, [":postid" => $postId]);
    return $res;

}

function getPostCommentsFromDB($postId) {
    // TODO check for permissions
    $sql = "select * from Comments where post_id = :postid order by tstamp;";
    /* @var $dbConn DBConn */
    $dbConn = DB::getInstance()->getConnection();
    $res = $dbConn->queryWithValues($sql, [":postid" => $postId]);
    return $res;
}

function addCommentToDB($postId, $message) {
    $sql = "insert into Comments (user_id, post_id, message) values (:user_id, :post_id, :comment_text);";
    /* @var $dbConn DBConn */
    $dbConn = DB::getInstance()->getConnection();
    $res = $dbConn->queryWithValues($sql, [
        ":user_id" => $_SESSION['userId'],
        ':post_id' => $postId,
        ":comment_text" => $message
    ]);
    return $res;
}


function addPostToDB($groupId, $message, $isCommentable) {
    $sql = "insert into Posts (user_id, group_id, contents, is_commentable) values (:user_id, :group_id, :contents, :is_commentable);
            select LAST_INSERT_ID();";

    /* @var $dbConn DBConn */
    $dbConn = DB::getInstance()->getConnection();
    $res = $dbConn->queryWithValues($sql, [
        ":user_id" => $_SESSION['userId'],
        ":group_id" => $groupId,
        ":contents" => $message,
        ":is_commentable" => $isCommentable
    ]);
    return $res;
}


function getPotentialMembers($userId, $groupId) {
    $allConnections = getUserConnections($userId);
    $potentialMembers = ["potentialMembers" => []];
    foreach ($allConnections['usersByAssociation'] as $assId => $userIds) {
        foreach($userIds as $assUserId) {
            if (!in_array($assUserId, $allConnections['usersByGroup'][$groupId] ?? []) && $assUserId != $userId) {
                $potentialMembers['potentialMembers'][] = $assUserId;
            }
        }
    }
    return $potentialMembers;
}


function getUserConnections($userId) {
    $connections = [
        'allUsers' => [],
        'usersByGroup' => [],
        'usersByAssociation' => [],
        'admins' => []
    ];

    $connectionSql = <<<EOD
select u.id as userid, NULL as groupid, ur.associationid 
from user_roles ur
join users u on ur.userid = u.id
where ur.associationid in (select associationid from user_roles where userid = :userid) or ur.associationid = 1
union all
select u.id as userid, gm.groupid, NULL as associationid
from group_membership gm
join users u on gm.userid = u.id
where gm.groupid in (select groupid from group_membership where userid = :userid)
EOD;

    /* @var $dbConn DBConn */
    $dbConn = DB::getInstance()->getConnection();
    $res = $dbConn->queryWithValues($connectionSql, [":userid" => $userId]);

    foreach($res as $connection) {
        if (!in_array($connection['userId'], $connections['allUsers'])) {
            $connections['allUsers'][] = $connection['userId'];
        }
        if (!is_null($connection['associationId'])) {
            if ($connection['associationId'] != '1') {
                if (!array_key_exists($connection['associationId'], $connections['usersByAssociation'])) {
                    $connections['usersByAssociation'][$connection['associationId']] = [];
                }
                if (!in_array($connection['userId'], $connections['usersByAssociation'][$connection['associationId']])) {
                    $connections['usersByAssociation'][$connection['associationId']][] = $connection['userId'];
                }
            } elseif (!in_array($connection['userId'], $connections['admins'])) {
                $connections['admins'][] = $connection['userId'];
            }
        }
        
        if (!is_null($connection['groupId'])) {
            if (!array_key_exists($connection['groupId'], $connections['usersByGroup'])) {
                $connections['usersByGroup'][$connection['groupId']] = [];
            }
            if (!in_array($connection['userId'], $connections['usersByGroup'][$connection['groupId']])) {
                $connections['usersByGroup'][$connection['groupId']][] = $connection['userId'];
            }
        }
    }

    return $connections;
}

function addUserToDB($email, $unencryptedPwd, $firstName, $lastName) {

    if (checkUserExists($email)) {
        // user already exists
        // .... redirect somewhere?
        echo "ERROR: USER ALREADY EXISTS";
    } else {
        $hashedPwd = password_hash($unencryptedPwd, PASSWORD_DEFAULT);
        $createUserSQL = "INSERT INTO users (email, firstname, lastname, password) VALUES (:email, :firstname, :lastname, :password);";
        $dbConn = DB::getInstance()->getConnection();
        $result = $dbConn->queryWithValues($createUserSQL, [
            ":email" => $email,
            ":firstname" => $firstName,
            ":lastname" => $lastName,
            ":password" => $hashedPwd
        ]);
        // On success, redirect back to home (FOR NOW)
        header("Location: /");
    }
}

function bulkAddUsersToDb($userList) {
    $params = [];
    $valuesStr = [];
    $add = function($param) use (&$valuesStr, &$params) {
        static $placeholders = [];
        $placeholder = ":param" . (sizeof($params) + 1);
        $params[$placeholder] = $param;
        $placeholders[] = $placeholder;
        if (sizeof($placeholders) % 4 == 0) {
            $valuesStr[] = '(' . implode(', ', $placeholders) . ")";
            $placeholders = [];
        }
    };

    foreach ($userList as $newUser) {
        foreach ($newUser as $field => $value) {
            if ($field == "pwd") {
                $value = password_hash($value, PASSWORD_DEFAULT);
            }
            $add($value);
        }
    }

    $insertSql = "insert into users (firstname, lastname, email, password) values " . implode(", ", $valuesStr) . ";";
    $dbConn = DB::getInstance()->getConnection();
    try {
        $dbConn->queryWithValues($insertSql, $params);
        echo "Batch create success!";
    }
    catch (\PDOException $e) {
        echo "Batch create failed";
    }
}

function logInUser($email, $unencryptedPwd) {

    if (checkUserExists($email)) {
        // user exists, verify login
        $userEmailSQL = "SELECT id, password from users where email = :email;";
        $dbConn = DB::getInstance()->getConnection();
        $res = $dbConn->queryWithValues($userEmailSQL, [":email" => $email]);
        if (password_verify($unencryptedPwd, $res[0]["password"])) {
            // password matches

            // check if hashing algo was changed/ if password needs to be rehashed as per recommendation here:
            // https://alexwebdevelop.com/php-password-hashing/
            if (password_needs_rehash($res[0]["password"], PASSWORD_DEFAULT)) {
                $newHashedPwd = password_hash($unencryptedPwd, PASSWORD_DEFAULT);
                $updateHashedPwdSQL = "UPDATE users SET password = :password where email = :email;";
                $dbConn->queryWithValues($updateHashedPwdSQL, [":password" => $newHashedPwd, ":email" => $email]);
            } else {
                // START A SESSION
                // https://www.w3schools.com/php/php_sessions.asp
                // send user back to home
                $_SESSION["userId"] = $res[0]["id"];
                if (empty($_SESSION['urlAfterLogin'])) {
                    $nextUrl = "/";
                } else {
                    $nextUrl = $_SESSION['urlAfterLogin'];
                    unset($_SESSION['urlAfterLogin']);
                }
                header("Location: " . $nextUrl);
            }
        } else {
            // invalid password, send back to login
            header("Location: /login");
        }
    } else {
        // User does not exist, redirect to create account (FOR NOW)
        header("Location: /createaccount");
    }

}

function checkUserHasCreatePermission($resp, $userId): array {
    $sql = <<<EOD
select ur.userid, ur.roleid, r.name
from user_roles ur
join roles r
on ur.roleid = r.id
where ur.userid = :userid
EOD;
    $resp['loggedIn'] = true;
    $dbConn = DB::getInstance()->getConnection();
    $res = $dbConn->queryWithValues($sql, [":userid" => $userId]);
    if ($res[0]['name'] == 'superuser') {
        $resp['hasCreatePermission'] = true;
    }

    return $resp;
}

function yoyParticipation($type) {

    if ($type == 'associations') {
        //
        $table = "condo_association";
        $where = "WHERE id <> 1";
    } else {
        // users
        $table = "users";
        $where = "WHERE isactive and id not in (select userid from user_roles where roleid = 1)";
    }
    $sql = <<<EOD
with by_year as (
    select 
    YEAR(createdon) as year,
    count(*) as num 
    from $table
    $where
    group by 1
)
select year,
sum(num) over (order by by_year.year) as active
from by_year
EOD;
    $dbConn = DB::getInstance()->getConnection();
    $res = $dbConn->queryWithValues($sql, []);
    return $res;
}

function getGroupNames() {
    /* @var $dbConn DBConn */
    $dbConn = DB::getInstance()->getConnection();
    $res = $dbConn->query("select name from con_group");
    return $res;
}

function createNewGroup($name, $description) {
    // create group
    $sql = "insert into con_group (name, description) values (:name, :description);";
    $dbConn = DB::getInstance()->getConnection();
    $res = $dbConn->queryWithValues($sql, [":name" => $name, ":description" => $description]);
    $sql = "select id from con_group where name = :name;";
    $newId = $dbConn->queryWithValues($sql, [":name" => $name])[0]['id'];
    // create user role for group as admin
    $sql = "insert into group_membership (userid, groupid, roleid) values (:userid, :groupid, 2);";
    $res = $dbConn->queryWithValues($sql, [":userid" => $_SESSION['userId'], ":groupid" => $newId]);
    echo "OK";
}

function getGroupsById() {
    $sql = "select cg.id, cg.name from con_group cg join group_membership gm on cg.id = gm.groupid where gm.userid = :userid;";
    $dbConn = DB::getInstance()->getConnection();
    $res = $dbConn->queryWithValues($sql, [":userid" => $_SESSION['userId']]);
    return $res;
}

/* =====================================================================
 *
 * HELPERS
 *
 * =====================================================================
 */

function checkUserExists(string $email) {
    $checkUserExistsSql = 'SELECT EXISTS(select email from users where email = :email) as "exists";';

    /* @var $dbConn DBConn */
    $dbConn = DB::getInstance()->getConnection();
    $res = $dbConn->queryWithValues($checkUserExistsSql, [":email" => $email]);
    return $res[0]["exists"];
}