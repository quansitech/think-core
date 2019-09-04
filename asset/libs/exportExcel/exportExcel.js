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
    this.options = opt;
    this.export_data = [];
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

    ExportExcel.prototype.checkIe = function () {
        var Sys = {};
        var ua = navigator.userAgent.toLowerCase();
        var s;
        (s = ua.match(/msie ([\d.]+)/)) ? Sys.ie = s[1] :
            (s = ua.match(/firefox\/([\d.]+)/)) ? Sys.firefox = s[1] :
                (s = ua.match(/chrome\/([\d.]+)/)) ? Sys.chrome = s[1] :
                    (s = ua.match(/opera.([\d.]+)/)) ? Sys.opera = s[1] :
                        (s = ua.match(/version\/([\d.]+).*safari/)) ? Sys.safari = s[1] : 0;
        if (!!window.ActiveXObject || "ActiveXObject" in window || Sys.ie) {
            return true;
        }
        return false;
    }

  ExportExcel.prototype.saveAs = function(obj, fileName){
      fileName = fileName || '下载';
      if (this.checkIe()) {
          //Js判断为IE浏览器
          var blob = new Blob([obj], {type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,charset=UTF-8"});
          saveAs(blob, fileName + ".xlsx");
          return false;
      }

    var tmpa = document.createElement("a");
    tmpa.download = fileName || "下载";
    tmpa.href = URL.createObjectURL(obj);
      $('body').append(tmpa);
    tmpa.click();
    setTimeout(function () {
        URL.revokeObjectURL(obj);
        $(tmpa).remove();
    }, 100);
  }

  ExportExcel.prototype.generateExcelBlob = function(data){
    var wb = { SheetNames: ['Sheet1'], Sheets: {}, Props: {} };
    wb.Sheets['Sheet1'] = xs.utils.json_to_sheet(data);
    var buffer = this.s2ab(xs.write(wb, this.wopts));
    return new Blob([buffer], { type: "application/octet-stream" });
  }

  ExportExcel.prototype.streamExport = function(rownum){
    if(this.options.before && typeof this.options.before == 'function'){
        this.options.before();
    }
    this.stream(1, rownum);
  }

  ExportExcel.prototype.stream = function(page, rownum){
      var obj = this;
      var reqType = this.options.reqType;
      var reqBody = this.options.reqBody;
      var query = 'page=' + page + '&rownum=' + rownum;
      var url = '';
      if (obj.options.url.indexOf('?') > 0) {
          url = obj.options.url + '&' + query;
      } else {
          url = obj.options.url + '?' + query;
      }
      fetch(url, {
          headers:{'Content-Type': 'application/x-www-form-urlencoded'},
          credentials: 'include',
          method:reqType,
          body:reqBody
      }).then(function(res){
          if(res.ok){
            return res.json();
          }
          else{
            throw 'something go error, status:' . res.status;
          }
      }).then(function(data) {
          if(data.length >0){
              obj.export_data = obj.export_data.concat(data);
              if(obj.options.progress && typeof obj.options.progress == 'function'){
                  obj.options.progress(page * rownum);
              }
              obj.stream(page+1, rownum);
          }
          else{
              obj.makeExcel(obj.export_data);
          }
      });

  }

  ExportExcel.prototype.makeExcel = function(data){
      if(this.options.after && typeof this.options.after == 'function'){
          this.options.after();
      }
      this.saveAs(this.generateExcelBlob(data), this.options.fileName + '.' + (this.wopts.bookType=="biff2"?"xls":this.wopts.bookType));
  }


  ExportExcel.prototype.export = function(){
    var obj = this;
    if(obj.options.before && typeof obj.options.before == 'function'){
        obj.options.before();
    }
    fetch(obj.options.url, {credentials: 'include'}).then(function(res) {
      if(res.ok){
        return res.json();
      }
      else{
        throw 'something go error, status:' . res.status;
      }
    }).then(function(data) {
      obj.makeExcel(data);

    }).catch(function(e) {
      console.log(e);
    });
  }

  window.ExportExcel = ExportExcel;
}(XLSX, window));
