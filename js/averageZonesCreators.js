function InternetZoneData(entity) {
	this.entity = entity;
	this.getData = function() {
		return {
			'Tiempo Promedio de Descarga': parseFloat(this.entity.avgDownloadTime / 1000).toFixed(2) + ' segs',
			'Promedio de señal del Emisor': parseFloat(this.entity.avgSignal).toFixed(2),
			'Cantidad de registros': this.entity.numRegs
		};
	};

	this.getColor = function() {
		var rgb = 'rgb(';
		var worstValue = 5000;
		rgb += parseInt(this.entity.avgDownloadTime * (255 / worstValue), 10) + ',';
		rgb += parseInt(204 - this.entity.avgDownloadTime * 204 / worstValue, 10) + ',0)';

		return rgb;
	};

	this.shouldPaintZone = function() {
		return this.entity.avgDownloadTime > 0;
	};
}

function CallsZoneData(entity) {
	this.entity = entity;
	this.getData = function() {
		return {
			'Tiempo Promedio de Conexión': parseFloat(this.entity.avgConnectionTime).toFixed(2) + ' segs',
			'Promedio de señal del Emisor': parseFloat(this.entity.avgSignal).toFixed(2),
			'Promedio de señal del Receptor': parseFloat(this.entity.avgRecSignal).toFixed(2),
			'Cantidad de registros': this.entity.numRegs
		};
	};

	this.getColor = function() {
		var rgb = 'rgb(';
		var worstValue = 15;
		rgb += parseInt(this.entity.avgConnectionTime * (255 / worstValue), 10) + ',';
		rgb += parseInt(204 - this.entity.avgConnectionTime * 204 / worstValue, 10) + ',0)';

		return rgb;
	};

	this.shouldPaintZone = function() {
		return this.entity.avgConnectionTime > 0;
	};
}

function SmsZoneData(entity) {
	this.entity = entity;
	this.getData = function() {
		return {
			'Tiempo Promedio de Envío': parseFloat(this.entity.avgSendingTime / 1000).toFixed(2) + ' segs',
			'Promedio de señal del Emisor': parseFloat(this.entity.avgSignal).toFixed(2),
			'Cantidad de registros': this.entity.numRegs
		};
	};

	this.getColor = function() {
		var rgb = 'rgb(';
		var worstValue = 10; //TODO: What the hell goes over here?
		rgb += parseInt(this.entity.avgSendingTime * (255 / worstValue), 10) + ',';
		rgb += parseInt(204 - this.entity.avgSendingTime * 204 / worstValue, 10) + ',0)';

		return rgb;
	};

	this.shouldPaintZone = function() {
		return false;
	};
}

function SignalZoneData(entity) {
	this.entity = entity;
	this.getData = function() {
		return {
			'Promedio de señal': parseFloat(this.entity.avgSignal).toFixed(2),
			'Cantidad de registros': this.entity.numRegs
		};
	};

	this.getColor = function() {
		var rgb = 'rgb(';
		var worstValue = 32;
		rgb += parseInt(worstValue - this.entity.avgSignal * (255 / worstValue), 10) + ',';
		rgb += parseInt(204 - this.entity.avgSignal * 204 / worstValue, 10) + ',0)';

		return rgb;
	};

	this.shouldPaintZone = function() {
		return this.entity.avgSignal > 0;
	};
}

function FailedInternetZoneData(entity) {
	this.entity = entity;
	this.getData = function() {
		return {
			'Porcentaje de pruebas fallidas': parseFloat(this.entity.avgFailures).toFixed(2),
			'Promedio de señal': parseFloat(this.entity.avgSignal).toFixed(2),
			'Cantidad de registros': this.entity.numRegs
		};
	};

	this.getColor = function() {
		var rainBow = new Rainbow();
		rainBow.setNumberRange(0, 101);
		rainBow.setSpectrum('green', 'red');
		return '#' + rainBow.colourAt(parseInt(Number(this.entity.avgFailures) * 100));
	};

	this.shouldPaintZone = function() {
		return this.entity.avgSignal > 0;
	};
}