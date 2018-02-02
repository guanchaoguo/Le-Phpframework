## MongoBD数据库脚本 ##

>脚本说明：添加索引操作
>以下为具体脚本：

    db.api_log.ensureIndex({"start_time":1})

    db.getCollection('cash_record').ensureIndex({"start_time":1,"end_time":1})

    db.getCollection('cash_record').ensureIndex({"pkey":1})

    db.getCollection('user_chart_info').ensureIndex({"start_time":1})