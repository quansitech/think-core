require('./label-select.less');
require('./lib/popup/css/popup.css');
require('./lib/popup/js/popup.js');
require('./lib/q.js');

function labelSelect(confObj){
	this.labelItemStr = '';
	this.labelListItemStr = '';
	this.url = confObj.url;
	this.addUrl = confObj.addUrl;
	this.removeUrl = confObj.removeUrl;
	this.reqData = confObj.reqData;
	this.addData = confObj.addData||{};
	this.removeData = confObj.removeData||{};
	this.res = null;
	this.selectedLabels = [];
	this.field = confObj.field;
	this.tips = confObj.tips||'';
	this.value = confObj.value;
	this.hash = Math.ceil(new Date().getTime() * Math.random() * Math.random());

	this.$el = $(confObj.el);
	this.$body = $('body');
	this.$selectMore = null;
	this.$iconCloseMask = null;
	this.$maskBox = null;
	this.$labelList = null;
	this.$labelSelected = null;
	this.$selectLabel = null;

	this.init();
}

labelSelect.prototype.init = function (){
	var that = this;
	this.$el.addClass('select-label-' + this.hash);
	this.$el = $('.select-label-' + this.hash);

	this.getData(this.url,this.reqData)
	.then(function (){
		that.initDom();
	})
	.then(function (){
		that.initVar();
	}).then(function (){
		that.initEvent();
		that.initModal();
	}).then(function (){
		that.initSelected();
		that.getSelectedStr();
		that.appendSelectedLabel();
	});
}

labelSelect.prototype.initVar = function (){
	this.$selectMore = this.$el.find('.select-more');
	this.$maskBox = $('#label-select-mask-' + this.hash);
	this.$iconCloseMask = this.$maskBox.find('.icon-close-mask');
	this.$labelList = this.$el.find('#label-list-' + this.hash);
	this.$labelSelected = this.$el.find('#label-selected-' + this.hash);
	this.$selectLabel = this.$maskBox.find('input[name="select-label"]');
}

labelSelect.prototype.initEvent = function(){
	var that = this;

	this.$iconCloseMask.on('click',function (){
		that.closeModal();
	});
    this.$selectMore.on('click',function (){
    	that.showModal();
    });

	this.$body.on('click','#label-select-mask-' + this.hash + ' .btn-add-label',function(){
    	that.addLabel()
    	.then(function (res){
    		if(res.status == 0){
				alert(res.info);
			}else{
				var renderStr = that.getRenderStr(res);
				that.$maskBox.find('.label-list').append(renderStr);
				that.$selectLabel.val('');
			}
		}).catch(function (xhr){
		});
    });

	this.$body.on('click','#label-select-mask-' + this.hash + ' .label-remove',function(){
		var $this = $(this);
		var id = $this.data('id');

    	that.removeLabel(id)
    	.then(function (){
    		$this.parent().fadeOut(200,function (){
				$this.parent().remove();
			});
    	});
    });

    this.removeLabelItem();
}

labelSelect.prototype.initDom = function (){
	if(!$('.popup-mask').length){
		this.$body.append('<div class="popup-mask hidden"></div>');
	}

	var initStr = '<div class="label-select-box">'+
	        	'<ul class="label-list" id="label-list-' + this.hash + '">' +
		        '</ul>' +
		        '<input type="hidden" value="'+ this.value +'" name=' + this.field + ' id="label-selected-'+ this.hash +'">' +
		        '<div class="tips-wrap">'+ 
		        	'<i class="select-more icon-add"></i><span class="label-tips">' + this.tips + '</span>'
		        '</div>' +
		    '</div>';

    var initMaskStr = '<div class="popup-mask-box label-select-mask hidden" id="label-select-mask-'+ this.hash +'">' +
        '<div class="popup-content">' +
            '<div class="popup-info">' +
                '<h2 class="popup-title"> ' +
                  '<span>添加标签</span>' +
                  '<a href="javascript:void(0)" class="icon-close-mask">' +
                    '<i class="icon-close popup-close"></i>' +
                  '</a>' +
                '</h2>' +
                '<div class="popup-wrapper">' +
                	'<form class="add-label text-center" action="" method="get">'+
						'<input type="text" style="display: none;">' +
                		'<div class="form-label">'+
                			'<div class="input-wrap">'+
                				'<input type="text" name="select-label" placeholder="新增标签" class="select-label">' +
                			'</div>' +
                			'<div class="btn-wrap">'+
                				'<button class="button-red btn-add-label" type="button">新增</button>' +
                			'</div>' +
                		'</div>'+
                	'</form>' +
                    '<ul class="label-list">' +
                    '</ul>' +
                '</div>' +
                '<div class="text-center pt15 popop-footer">' +
                    '<button type="button" class="button-red btn-sm select-label-confirm icon-close-mask">确定</button>' +
                '</div>' +
            '</div>' +
        '</div>' +
    '</div>';

    return Q.all([
    	this.$el.append(initStr),
    	this.$body.append(initMaskStr),
    	]);
}

labelSelect.prototype.initSelected = function(){
	var that = this;
	var valArr = $.trim(this.$labelSelected.attr('value')).split(',');

	this.selectedLabels = [];
	this.res.forEach(function (v){
		valArr.forEach(function (val){
			if(v.id == val){
				that.selectedLabels.push(v);
			}
		});
	});
}

labelSelect.prototype.appendSelectedLabel = function(){
	this.$labelList.html(this.labelListItemStr);
}

labelSelect.prototype.getSelectedStr = function(){
	var that = this;
	that.labelListItemStr = '';
	this.selectedLabels.forEach(function (v){
		that.labelListItemStr += '<li data-id='+ v.id +'>'+
						        	'<span class="title">' + v.name + '</span>'+
						        	'<i class="label-delte icon-close" data-id="'+ v.id +'"></i>'+
						        '</li>';
	});
}

labelSelect.prototype.getSelectedLabel = function (){
	var that = this;
	this.selectedLabels = [];
	this.$maskBox.find('.label-list').find('.label-select').each(function (){
		var $this = $(this);
		var isChecked = $this.prop('checked');
		if(isChecked){
			that.selectedLabels.push({
				id: $this.data('id'),
				name: $this.data('name'),
			});
		}
	});
}

labelSelect.prototype.getSelectedIds = function (){
	var ids = [];
	this.selectedLabels.map(function (v){
		ids.push(v.id);
	});
	return ids;
}

labelSelect.prototype.updateSelectData = function (){
	var ids = this.getSelectedIds(this.selectedLabels);
	this.$labelSelected.attr('value',ids.join(','));
}

labelSelect.prototype.preventWindowScroll = function(e){
	return false;
}

labelSelect.prototype.showModal = function (){
	var that = this;
	var ids = this.getSelectedIds(this.selectedLabels);
	this.$maskBox.popup('show',{
		onShowBefore: function (){
			window.addEventListener('scroll',labelSelect.prototype.preventWindowScroll);
			that.$maskBox
			.find('.label-list')
			.find('.label-select')
			.prop({checked: false})
			.each(function (){
				var $this = $(this);
				if(ids.indexOf($this.data('id')) > -1){
					$this.prop({checked: true});
				}
			});
		}
	});
}

labelSelect.prototype.closeModal= function (){
	var that = this;
    this.$maskBox.popup('hide',{
    	onCloseBefore: function (){
			window.removeEventListener('scroll',labelSelect.prototype.preventWindowScroll);
			that.getSelectedLabel();
    		that.updateSelectData();
    		that.getSelectedStr();
    		that.appendSelectedLabel();
    		if(that.$maskBox.attr('id') != 'label-select-mask-' + that.hash){
    			return false;
    		}
    	}
    });
}

labelSelect.prototype.removeLabelItem = function(){
	var that = this;

	this.$body.on('click','#label-list-' + this.hash + ' .label-delte',function (){
		var $this = $(this);
		var removeId = $this.data('id');
		$this.parent().fadeOut(200);
		that.selectedLabels = that.selectedLabels.filter(function (v){
			return v.id != removeId;
		});
		that.updateSelectData(that.selectedLabels);
	});
}

labelSelect.prototype.initModal = function (){
	var that = this;

	var renderStr = this.getRenderStr(this.res);
	this.$maskBox.find('.label-list').html(renderStr);
}

labelSelect.prototype.getData = function (){
	var that = this;
	return get(this.url,this.reqData)
    .then(function (res){
    	that.res = res;
		return res;
    }).catch(function (xhr){
    	alert('获取标签失败！');
    });
}

labelSelect.prototype.getRenderStr = function (res){
	var that = this;
	that.labelItemStr = '';
	var dataArr = null;

	//新增的时候返回对象，否则返回数组
	if(!Array.isArray(res)){
		dataArr = [res];
	}else{
		dataArr = res;
	}
	dataArr.forEach(function (v){
    	that.labelItemStr += '<li class="label-select-item" data-id='+ v.id +'>'+
                    '<label>' + 
                      '<input type="checkbox" class="label-select" data-id='+ v.id +' data-name='+ v.name +'>' +
                      '<span class="title">'+ v.name +'</span>' +
                    '</label>' +
                    '<i class="label-remove icon-close" title="永久删除？" data-id="'+ v.id +'" alt=""></i>'+
                  '</li>';
	});
	return this.labelItemStr;
} 

labelSelect.prototype.addLabel = function (){
	this.addData.labelName = this.$selectLabel.val();
	return get(this.addUrl,this.addData);
}

labelSelect.prototype.removeLabel = function (id){
	this.removeData.id = id;
	return get(this.removeUrl,this.removeData);
}

module.exports = {
	labelSelect: labelSelect
};















function ajax(url,data,type){
	var type = type||'get';
	var url = url||'';
	var data = data||{};

    var defer = Q.defer();

	$.ajax({
    	type: type,
    	url: url,
    	data: data,
    	success: function (res){
    		defer.resolve(res);
    	},
    	error: function (xhr,m,s){
    		defer.reject(xhr,m,s);
    	},
    });
    return defer.promise;
}
function get(url,data){
	return ajax(url,data,'get');
}
function post(url,data){
	return ajax(url,data,'post');
}