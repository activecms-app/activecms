
class ReferenceSelect {
	constructor(field) {
		this.field = field;
		this.class = '';
		this.objecttype = '';
		if( this.field.hasAttribute('data-class') )
		{
			this.class = this.field.getAttribute('data-class');
		}
		if( this.field.hasAttribute('data-type') )
		{
			this.type = this.field.getAttribute('data-type');
		}

		this.searchResult = document.createElement('div');
		this.searchResult.setAttribute('class', 'dropdown-menu');
		var empty = this.searchResult.appendChild(this.createItem({label: 'Sin resultados', value: ''}));
		this.searchInput = document.createElement('input');
		this.searchInput.setAttribute('class', 'form-control');
		this.searchInput.addEventListener('click', (event) => {});
		this.searchInput.addEventListener('input', (event) => {
			if( this.searchInput.value.length > 2 )
			{
				console.log('/active/reference?q=' + this.searchInput.value + '&c=' + this.class + '&t=' + this.objecttype);
				fetch('/active/reference?q=' + this.searchInput.value + '&c=' + this.class + '&t=' + this.objecttype,
					{
						method: 'GET',
						headers: {
							Accept: 'application/json',
						}
					})
					.then( 
						(response) => {
							if( response.ok ) 
							{
								response.json().then(json => {
									this.updateItems(json);
									this.dropdown.show();
								});
							}
						}
					);
			}
		});
		var searchGroup = document.createElement('div');
		searchGroup.setAttribute('class', 'input-group');
		searchGroup.innerHTML = '<span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>';
		searchGroup.append(this.searchInput);
		this.selectedItem = document.createElement('div');
		this.selectedItem.setAttribute('class', 'input-group');
		this.selectedLabel = document.createElement('div');
		this.selectedLabel.setAttribute('class', 'form-control');
		if( this.field.hasAttribute('data-title') )
		{
			this.selectedLabel.innerHTML = this.field.getAttribute('data-title');
		}
		else
		{
			this.selectedLabel.innerHTML = this.field.value;
		}
		this.selectedItem.append(this.selectedLabel);
		var selectedClose = document.createElement('button');
		selectedClose.setAttribute('type', 'button');
		selectedClose.setAttribute('class', 'input-group-text');
		selectedClose.innerHTML = '<i class="fa-solid fa-xmark"></i>';
		selectedClose.addEventListener('click', (event) => {
			this.selectedItem.style.display = 'none';
			this.field.value = '';
			this.searchInput.value = '';
			this.searchInput.parentNode.style.display = 'flex';
			this.searchInput.focus();
		});
		this.selectedItem.append(selectedClose);

		var searchBox = document.createElement('div');
		searchBox.append(this.selectedItem);
		searchBox.append(searchGroup);
		searchBox.append(this.searchResult);
		this.dropdown = new bootstrap.Dropdown(searchGroup);

		if( this.field.value.length )
		{
			this.searchInput.parentNode.style.display = 'none';
		}
		else
		{
			this.selectedItem.style.display = 'none';
		}
		this.field.style.display = 'none';
		this.field.parentElement.append(searchBox);

	}

	updateItems(data) {
		this.searchResult.innerHTML = '';
		const keys = Object.keys(data);
		let count = 0;
		for (let i = 0; i < keys.length; i++) {
			const key = keys[i];
			const entry = data[key];
			this.searchResult.appendChild(this.createItem({label: entry.label, value: entry.value}));
			count++;
		}
		if( count )
		{
			this.searchResult.querySelectorAll('.dropdown-item').forEach((item) => {
				item.addEventListener('click', (event) => {
					this.selectItem(event.target.getAttribute('data-value'), event.target.innerHTML);
					this.dropdown.hide();
				});
			});
		}
		else
		{
			this.searchResult.appendChild(this.createItem({label: 'Sin resultados', value: ''}));
		}
		return count;
	}

	createItem(item)
	{
		let div = document.createElement('div');
		if( item.value )
		{
			div.innerHTML = `<button type="button" class="dropdown-item" data-value="${item.value}">${item.label}</button>`;
		}
		else
		{
			div.innerHTML = `<button type="button" class="dropdown-item disabled" data-value="${item.value}">${item.label}</button>`;
		}
		return div.firstChild;
	}

	selectItem(value, label)
	{
		this.field.value = value;
		this.selectedLabel.innerHTML = label;
		this.searchInput.parentNode.style.display = 'none';
		this.selectedItem.style.display = 'flex';
	}
}

function removeDiacritics(str) {
	return str
		.normalize('NFD')
		.replace(/[\u0300-\u036f]/g, '');
}