(function(xs, window){
  'use strict';
  function ExportExcel(opt){
    this.wopts = {
      bookType: 'xlsx',
      bookSST: false,
      type: 'binary',
      reqType:'GET',
      reqBody:''
    };
    if (typeof opt.reqType === 'undefined')
    {
        opt.reqType=this.wopts.reqType;
    }
    if (typeof opt.url === 'string'){
        opt.url = [{sheetName:'Sheet1',url:opt.url}];
    }
    this.options = opt;
    this.export_data = [];
    this.wb = { SheetNames: [], Sheets: {}, Props: {} };
    this.execCount = this.options.url.length;
  }

  ExportExcel.prototype.s2ab = function(s){
    if (typeof ArrayBuffer !== 'undefined') {
        var buf = new ArrayBuffer(s.length);
        var view = new Uint8Array(buf);
        for (var i = 0; i != s.length; ++i) view[i] = s.charCodeAt(i) & 0xFF;
        return buf;
    } else {
        var buf = new Array(s.length);
        for (var i = 0; i != s.length; ++i) buf[i] = s.charCodeAt(i) & 0xFF;
        return buf;
    }
  }

  ExportExcel.prototype.saveAs = function(obj, fileName){
    var tmpa = document.createElement("a");
    tmpa.download = fileName || "下载";
    tmpa.href = URL.createObjectURL(obj);
    tmpa.click();
    setTimeout(function () {
        URL.revokeObjectURL(obj);
    }, 100);
  }

  ExportExcel.prototype.generateExcelBlob = function(){
    var buffer = this.s2ab(xs.write(this.wb, this.wopts));
    return new Blob([buffer], { type: "application/octet-stream" });
  }

  ExportExcel.prototype.pushSheet = function(data, sheetName){
    this.wb.Sheets[sheetName] = xs.utils.json_to_sheet(data);
    this.execCount--;
    this.execCount === 0 && this.makeExcel();
   }

  ExportExcel.prototype.fetchFun = function(fetch_url, url, init, sheetName, type, page, rownum){
      var obj = this;
      fetch(fetch_url, init).then(function(res){
          if(res.ok){
            return res.json();
          }
          else{
            throw 'something go error, status:' . res.status;
          }
      }).then(function(data) {
          if(data.status != undefined && data.status == 0){
              if(obj.options.error && typeof obj.options.error == 'function'){
                  obj.options.error(data.info);
                  return;
              }
          }

          if(!obj.export_data[sheetName]) obj.export_data[sheetName] = [];
          if (type === 'stream'){
          if(data.length >0){
              obj.export_data[sheetName] = obj.export_data[sheetName].concat(data);
              if(obj.options.progress && typeof obj.options.progress == 'function'){
                  obj.options.progress(page * rownum);
              }
              obj.stream(page+1, rownum, url, sheetName);
          }
          else{
              obj.pushSheet(obj.export_data[sheetName], sheetName);
            }
          } else{
              obj.pushSheet(data, sheetName);
          }

    }).catch(function(e) {
        console.log(e);
      });
  }

  ExportExcel.prototype.streamExport = function(rownum){
    var obj = this;
    if(obj.options.before && typeof obj.options.before == 'function'){
        obj.options.before();
    }
    for (var i=0; i<obj.options.url.length; i++){
        var sheetName = obj.options.url[i].sheetName ? obj.options.url[i].sheetName : "Sheet"+(i+1);
        obj.wb.SheetNames.push(sheetName);
        obj.stream(1, rownum, obj.options.url[i].url, sheetName)
    }
  }

  ExportExcel.prototype.stream = function(page, rownum, url, sheetName){
    var obj = this;
    var reqType = this.options.reqType;
    var reqBody = this.options.reqBody;
    var query = 'page=' + page + '&rownum=' + rownum + '&ajax=1';
    var fetch_url = '';
    if (url.indexOf('?') > 0) {
        fetch_url = url + '&' + query;
    } else {
        fetch_url = url + '?' + query;
    }
    return obj.fetchFun(fetch_url, url, {
        headers:{'Content-Type': 'application/x-www-form-urlencoded'},
        credentials: 'include',
        method:reqType,
        body:reqBody
    }, sheetName, 'stream', page, rownum);


  }

  ExportExcel.prototype.makeExcel = function(){
      if(this.options.after && typeof this.options.after == 'function'){
          this.options.after();
      }
      this.saveAs(this.generateExcelBlob(), this.options.fileName + '.' + (this.wopts.bookType=="biff2"?"xls":this.wopts.bookType));
  }


  ExportExcel.prototype.export = function(){
    var obj = this;
    if(obj.options.before && typeof obj.options.before == 'function'){
        obj.options.before();
    }
    for (var i=0; i<obj.options.url.length; i++){
        var sheetName = obj.options.url[i].sheetName ? obj.options.url[i].sheetName : "Sheet"+(i+1);
        obj.wb.SheetNames.push(sheetName);
        obj.fetchFun(obj.options.url[i].url, '', {
            credentials: 'include'
        }, sheetName, '', '', '', '')
      }

  }

  window.ExportExcel = ExportExcel;
}(XLSX, window));
