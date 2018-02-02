define({ "api": [
  {
    "type": "post",
    "url": "/lebogame/user/token",
    "title": "玩家获取token认证",
    "description": "<p>玩家获取token认证</p>",
    "group": "Auth",
    "permission": [
      {
        "name": "none"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "user_name",
            "description": "<p>用户名 chensj</p>"
          }
        ]
      }
    },
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "optional": false,
            "field": "Accept",
            "description": "<p>http头协议值为 'application/vnd.pt.lebogame.v2.2+json'</p>"
          }
        ]
      }
    },
    "version": "2.2.0",
    "success": {
      "examples": [
        {
          "title": "成功返回:",
          "content": "{\n\"code\": 0,\n\"text\": \"认证成功\",\n\"result\": {\n\"token\": \"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcGxhdGZvcm0uZGV2L2FwaS9sZWJvZ2FtZS91c2VyL3Rva2VuIiwiaWF0IjoxNDk4NDQ2MjYxLCJleHAiOjE0OTg2NjIyNjEsIm5iZiI6MTQ5ODQ0NjI2MSwianRpIjoiOHpJMFU4N2lNTTVOMURiMSIsInN1YiI6OTI2MTMxfQ.5IozZ8tRltebGGQrhfhiQ1Srf2KWEJGWBjNqDw8o3o4\"\n}\n}",
          "type": "json"
        },
        {
          "title": "失败返回:",
          "content": "{\n  \"code\": 400,\n   \"text\":'',\n   \"result\":''\n}",
          "type": "json"
        }
      ]
    },
    "filename": "app/Http/Controllers/Game/AuthController.php",
    "groupTitle": "Auth",
    "name": "PostLebogameUserToken"
  },
  {
    "type": "post",
    "url": "/lebogame/banner",
    "title": "游戏端获取banner",
    "description": "<p>游戏端获取banner</p>",
    "group": "Banner",
    "permission": [
      {
        "name": "JWT"
      }
    ],
    "version": "2.2.0",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "optional": false,
            "field": "Accept",
            "description": "<p>http头协议 application/vnd.pt.lebogame.v2.2+json</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "token",
            "description": "<p>认证token</p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "label",
            "description": "<p>所属平台,0为PC，1为横版，2为竖版，默认为0</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "成功返回:",
          "content": "{\n    \"code\": 0,\n    \"text\": \"操作成功\",\n    \"result\": {\n        \"data\": [\n            {\n                \"title\": \"wwwaa\",//标题名称\n                \"url\": \"http://www.1631.com\",//url地址\n                \"full_banner\": \"http://images.dev/./upload/img/2017/06/21/2017062107172953.jpg\"//banner图片地址\n            }\n        ]\n    }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "app/Http/Controllers/Game/GamePlatformBannerController.php",
    "groupTitle": "Banner",
    "name": "PostLebogameBanner"
  },
  {
    "type": "post",
    "url": "/lebogame/logo",
    "title": "游戏端获取logo",
    "description": "<p>游戏端获取logo</p>",
    "group": "Logo",
    "permission": [
      {
        "name": "JWT"
      }
    ],
    "version": "2.2.0",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "optional": false,
            "field": "Accept",
            "description": "<p>http头协议 application/vnd.pt.lebogame.v2.2+json</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "token",
            "description": "<p>认证token</p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "label",
            "description": "<p>所属平台,0为PC，1为横版，2为竖版，默认为0</p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "type",
            "description": "<p>类型 1:平台logo ,0：厅主logo ，默认为0</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "成功返回:",
          "content": "{\n    \"code\": 0,\n    \"text\": \"操作成功\",\n    \"result\": {\n        \"data\": [\n            {\n                \"title\": \"wwwaa\",//标题名称\n                \"full_logo\": \"http://images.dev/./upload/img/2017/06/21/2017062107172953.jpg\"//Logo图片地址\n            }\n        ]\n    }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "app/Http/Controllers/Game/GamePlatformLogoController.php",
    "groupTitle": "Logo",
    "name": "PostLebogameLogo"
  }
] });
