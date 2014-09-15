function Filters() {
    var filters = {};

    this.setPhoneNumber = function(num) {
        filters.phoneNumber = num;
    };

    this.setArea = function(lat1, lon1, lat2, lon2) {
        filters['lat1'] = lat1;
        filters['lon1'] = lon1;
        filters['lat2'] = lat2;
        filters['lon2'] = lon2;
    };

    this.setOperator = function(operator) {
        filters.operator = operator;
    };

    this.getFilters = function() {
        return _.chain(filters).map(function(val, key) { return key + "=" + val; })
            .reduce(function(memo, val) { return memo + '&' + val; }).value();
    };
}