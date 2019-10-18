(function(zip, create, window){
    function DownloadFileZip(url, filename, gid){
        this.pageSize = 1;
        this.loadData = [];
        this.queue = null;
        this.query = this.getQueryData();
        this.count = 0;
        this.pageNum = 1;
        this.currentPiece = 0;
        this.isInit = true;
        this.lastLoaded = 0;
        this.totalPiece = 0;
        var error = false;
        this.folderArr = [];
        this.zip = new zip();
        this.downloadNum = 1;
        this.url = url;
        this.filename = filename;
        this.gid = gid;
        this.selectIds = this.getSelectIds();
    }
    DownloadFileZip.prototype.getQueryData = function () {
        this.query = $('.builder .search-input').serialize();
        this.query = this.query.replace(/(&|^)(\w*?\d*?\-*?_*?)*?=?((?=&)|(?=$))/g, '');
        this.query = this.query.replace(/(^&)|(\+)/g, '');
        return this.query;
    }
    DownloadFileZip.prototype.noData = function () {
        this.showCloseBtn();
        alert('暂无数据');
    }
    DownloadFileZip.prototype.showCloseBtn = function () {
        $('#cancel-download-queue'+this.gid).removeClass('hidden');
        $('#download-complete'+this.gid).addClass('hidden');
    }
    DownloadFileZip.prototype.getCount = function() {
        var data = this.query + '&selectIds=' + this.selectIds;
        let that = this;
        $.ajax({
            type: 'get',
            async: false,
            url: this.url,
            data: data,
            success: function (res) {
                that.count = res.count;
                that.pageSize = res.pageSize;
                that.totalPiece = Math.ceil(that.count / that.pageSize);
            }
        });
    }
    DownloadFileZip.prototype.export = function() {
        var data = this.query + '&page=' + this.pageNum + '&selectIds=' + this.selectIds;
        let that = this;
        $.ajax({
            type: 'get',
            async: false,
            url: this.url,
            data: data,
            success: function (res) {
                that.pageNum++;
                if (that.count === 0 && that.pageNum == 1) {
                    that.noData();
                    return false;
                }
                that.currentPiece++;
                that.getFileData(res.list);
                that.queueInit();
            },
            fail: function () {
                alert('下载失败');
            }
        });
    }

    DownloadFileZip.prototype.initDownloadBar = function () {
        $('.progress-bar').css({
            width: 0
        });
        $('.progrss-num').text('0%');
    }
    DownloadFileZip.prototype.queueInit = function () {
        // console.log(this.loadData);
        this.that = this;
        this.queue = new create.LoadQueue(true);
        this.queue.loadManifest(this.loadData);
        //修改
        this.queue.on("complete", this.handleComplete, this);
        this.queue.on("fileload", this.handleFileLoad, this);
        this.queue.on("progress", this.handleProgress, this);
    }
    DownloadFileZip.prototype.getFileData = function (res) {
        let that = this;
        this.loadData = [];
        res.forEach(function (val) {
            var loadDataItem = {
                id: val.id,
                name: val.name,
                src: val.url,
                suffix: val.suffix,
                crossOrigin: true,
                type: createjs.AbstractLoader.SOUND
            };
            that.loadData.push(loadDataItem);
        });
    }
    DownloadFileZip.prototype.handleProgress = function (a) {
        //算法： 总模块的进度-1 + 当前模块进度的百分比
        var loaded = (this.currentPiece - 1) / this.totalPiece * 100 + a.loaded * 100 / this.totalPiece;
        loaded = parseInt(loaded);
        $('.progress-bar').css({
            width: loaded + '%'
        });
        $('.progrss-num').text(loaded + '%');
    }
    DownloadFileZip.prototype.handleFileLoad = function (e) {
        // console.log(e);
        this.appendFile2Zip(e.item);
    }
    DownloadFileZip.prototype.handleComplete = function () {
        if (this.totalPiece > this.currentPiece) {
            // //继续下载
            if ($('#comm-file-export-modal'+this.gid).data('show')) {
                this.export();
            }
        } else {
            // //下载结束
            this.createZip();
            $('#cancel-download-queue'+this.gid).addClass('hidden');
            $('#download-complete'+this.gid).removeClass('hidden');
        }
    }
    DownloadFileZip.prototype.getPhotoOrder = function () {
        return this.downloadNum++;
    }
    DownloadFileZip.prototype.appendFile2Zip = function (v) {
        // loadData.forEach(function (v) {
        var img = this.queue.getResult(v.id, true);
        if (!img) {
            error = true;
            return false;
        }
        if (typeof v.suffix != 'string') {
            v.suffix = '';
        }
        if (!v.suffix) {
            v.suffix = 'mp3';
        }
        //创建文件夹
        // folder = zip.folder(v.name + '_' + v.name);
        // folder.file(v.create_date + '_' + v.m[0],img, {blob: true});
        this.zip.file( v.name + '_'+v.id+'_'+this.getPhotoOrder()+'.' + v.suffix.toLowerCase(), img);
        // });
    }
    DownloadFileZip.prototype.createZip = function () {
        var strTime = this.formatDate();
        var zipName = this.filename+'-'+strTime+'.zip';
        $('#myModalLabel'+this.gid).text('生成压缩包');
        var $progressBar = $('.progress-bar'),
            $progressNum = $('.progrss-num');
        $progressBar.css({
            transition: 'none',
            width: '0%'
        });
        $progressNum.text('0%');
        this.zip.generateAsync({type: "blob", streamFiles: true}, function (metadata) {
            var loaded = metadata.percent.toFixed(2);
            $progressBar.css({
                width: loaded + '%'
            });
            $progressNum.text(loaded + '%');
        })
            .then(function (content) {
                saveAs(content, zipName);
            });
    }
    DownloadFileZip.prototype.formatDate = function () {
        var now = new Date();
        var year = now.getFullYear();
        var month = now.getMonth() + 1;
        var date = now.getDate();
        var hour = now.getHours();
        var minute = now.getMinutes();
        var second = now.getSeconds();
        return year + "-" + month + "-" + date + " " + hour + "_" + minute + "_" + second;
    }
    DownloadFileZip.prototype.getSelectIds = function () {
        var selectIds = '';
        $('.ids').each(function (index,value) {
            if($(this).prop('checked')){
                selectIds += $(this).val() + ','
            }
        });
        selectIds = selectIds.slice(0,-1);
        return selectIds;
    }
    DownloadFileZip.prototype.entrance = function () {
        $('#myModalLabel'+this.gid).text('下载队列');
        var query = this.getQueryData();
        selectIds = this.getSelectIds();
        // if(!selectIds){
        //     alert('请先勾选口译练习');
        //     return ;
        // }
        this.getCount();
        if (this.totalPiece == 0) {
            this.noData();
            return false;
        }
        this.export();
        this.initDownloadBar();
        $('#comm-file-export-modal'+this.gid).modal('show').data('show', true);
        this.showCloseBtn();
    }
    window.DownloadFileZip = DownloadFileZip;
}(JSZip, createjs, window));









