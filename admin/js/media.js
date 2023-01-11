
class MediaSelect {
	constructor(id, field, url) {
		this.field = field;
		this.url = url;
		this.id = id

		this.hiddenFileInput = document.createElement("input");
		this.hiddenFileInput.setAttribute("type", "file");
		this.hiddenFileInput.setAttribute("tabindex", "-1");
		this.hiddenFileInput.style.visibility = "hidden";
		this.hiddenFileInput.style.position = "absolute";
		this.hiddenFileInput.style.top = "0";
		this.hiddenFileInput.style.left = "0";
		this.hiddenFileInput.style.height = "0";
		this.hiddenFileInput.style.width = "0";
		this.hiddenFileInput.addEventListener("change", () => {
			let { files } = this.hiddenFileInput;
			if (files.length) {
				for (let file of files) {
					this.selectedLabel.innerHTML = file.name;
					let formData = new FormData();
					formData.append('file', file);
					formData.append('id', this.id);
					var xhr = new XMLHttpRequest();
					xhr.responseType = 'json';
					xhr.onload = (e) => {
						this.completeHandler(xhr, e)
					};
					//ajax.upload.addEventListener("progress", this.progressHandler, false);
					//ajax.addEventListener("load", this.completeHandler, false);
					//ajax.addEventListener("error", this.errorHandler, false);
					//ajax.addEventListener("abort", this.abortHandler, false);
					xhr.open("POST", this.url);
					xhr.send(formData);
				}
			}
		});

		this.uploadButton = document.createElement('button');
		this.uploadButton.setAttribute("type", "button");
		this.uploadButton.setAttribute('class', 'input-group-text');
		this.uploadButton.innerHTML = '<i class="fas fa-file-upload"></i>';
		this.uploadButton.addEventListener('click', (event) => {
			this.hiddenFileInput.click();
		});
		this.deleteButton = document.createElement('button');
		this.deleteButton.setAttribute("type", "button");
		this.deleteButton.setAttribute('class', 'input-group-text');
		this.deleteButton.innerHTML = '<i class="fas fa-times"></i>';
		this.deleteButton.addEventListener('click', (event) => {
			this.field.value = 'eliminado';
			this.selectedLabel.innerHTML = '&nbsp;';
			this.deleteButton.style.display = 'none';
			this.uploadButton.style.display = 'block';
		});
		this.selectedLabel = document.createElement('div');
		this.selectedLabel.setAttribute('class', 'form-control');
		if( this.field.value.length ) {
			this.selectedLabel.innerHTML = this.field.value;
			this.uploadButton.style.display = 'none';
		} else {
			this.selectedLabel.innerHTML = '&nbsp;';
			this.deleteButton.style.display = 'none';
		}
		var mediaGroup = document.createElement('div');
		mediaGroup.setAttribute('class', 'input-group');
		mediaGroup.append(this.selectedLabel);
		mediaGroup.append(this.deleteButton);
		mediaGroup.append(this.uploadButton);
		mediaGroup.append(this.hiddenFileInput);

		this.field.style.display = 'none';
		this.field.parentElement.append(mediaGroup);
	}

	progressHandler(event) {

	}

	completeHandler(xhr, e) {
		const data = xhr.response;
		if( data.length == 0 ) return;
		if( 'path' in data[0] ) {
			this.field.value = data[0]['path'];
			this.selectedLabel.innerHTML = this.field.value;
			this.uploadButton.style.display = 'none';
			this.deleteButton.style.display = 'block';
		}
	}

	errorHandler(event) {

	}

	abortHandler(event) {

	}
}
