<?php

namespace Utils\DB;

class DbAccessColumns {
    public const columnMapping = [
        "id" => [
            "type" => "string",
        ],
        "email" => [
            "type" => "string",
        ],
        "firstname" => [
            "type" => "string",
            "name" => "firstName"
        ],
        "lastname" => [
            "type" => "string",
            "name" => "lastName"
        ],
        "password" => [
            "type" => "string",
        ],
        "isactive" => [
            "type" => "bool",
            "name" => "isActive"
        ],
        "createdon" => [
            "type" => "string",
            "name" => "createdOn"
        ],
        "exists" => [
            "type" => "bool",
            "name" => "exists"
        ],
        "name" => [
            "type" => "string"
        ],
        "description" => [
            "type" => "string"
        ],
        "associationid" => [
            "type" => "string",
            "name" => "associationId"
        ],
        "datebuilt" => [
            "type" => "string",
            "name" => "dateBuilt"
        ],
        "address" => [
            "type" => "string"
        ],
        "ownerid" => [
            "type" => "string",
            "name" => "ownerId"
        ],
        "floor" => [
            "type" => "string",
        ],
        "door" => [
            "type" => "string",
            "name" => "doorNum"
        ],
        "userid" => [
            "type" => "string",
            "name" => "userId"
        ],
        "roleid" => [
            "type" => "string",
            "name" => "roleId"
        ],
        "year" => [
            "type" => "int",
            "name" => "year"
        ],
        "active" => [
            "type" => "int",
            "name" => "active"
        ],
        "associations" => [
            "type" => "string",
            "name" => "associations"
        ],
        "roles" => [
            "type" => "string",
            "name" => "roles"
        ],
        "numcondos" => [
            "type" => "int",
            "name" => "numCondos"
        ],
        "nummembers" => [
            "type" => "int",
            "name" => "numMembers",
        ],
        "numadmins" => [
            "type" => "int",
            "name" => "numAdmins"
        ],
        "groupid" => [
            "type" => "string",
            "name" => "groupId"
        ],
        "post_id" => [
            "type" => "string",
            "name" => "postId"
        ],
        "user_id" => [
            "type" => "string",
            "name" => "userId"
        ],
        "groupname" => [
            "type" => "string",
            "name" => "groupName"
        ],
        "contents" => [
            "type" => "string",
            "name" => "contents"
        ],
        "is_commentable" => [
            "type" => "bool",
            "name" => "isCommentable"
        ],
        "tstamp" => [
            "type" => "string",
            "name" => "postedOn"
        ],
        "numcomments" => [
            "type" => "string",
            "name" => "numComments"
        ]
    ];
}
