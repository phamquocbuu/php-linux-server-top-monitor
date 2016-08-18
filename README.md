# ServerStat
ServerStat is a server monitoring software that tracks cpu, mem and swap.

It's a web server and daemon that saves monitoring statistics and allows access it via ajax request.

**serverstat** is a standalone web server that reads data from storage and outputs it in web.
**serverstatd** is a daemon that keeps system information and stores it in storage.

## Configuration
File **serverstat.conf**:
* **storage** - can be `file` only.
* **period** - time between checks system status.
* **typeperf** - a counter name for typeperf utility.

## Starting
Run daemon **serverstatd**.
Run server **serverstat** and provide port (if you want) of server: `serverstat --port 83`.

Typical serverstat response on any request is:
```json
{  
   "processors":4,
   "processor_load":24,
   "memory":{  
      "total":"8499281920",
      "free":"2103896",
      "busy":8497178024
   },
   "swap":{  
      "total":0,
      "free":"0",
      "busy":0
   },
   "tasks":{  
      "total":105,
      "running":0
   },
   "uptime":30488
}
```
