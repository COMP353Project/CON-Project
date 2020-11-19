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
        ]
    ];
}
