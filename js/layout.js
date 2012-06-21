(function() {
    var Dom = YAHOO.util.Dom,
        Event = YAHOO.util.Event;

    Event.onDOMReady(function() {
        var layout = new YAHOO.widget.Layout({
            units: [
                { position: 'top', height: 75, body: 'description', header: 'Application description', gutter: '5px', collapse: true, resize: true },
                { position: 'right', header: 'Repository structure', width: 300, resize: true, gutter: '0px 5px', collapse: true, scroll: true, body: 'right', animate: true, footer: 'rightFooter' },
                { position: 'right', header: 'logger', width: 300, resize: false, gutter: '0px 5px', collapse: true, scroll: true, body: 'logger', animate: true },
                { position: 'bottom', header: '', height: 50, resize: false, body: 'bottom', gutter: '5px', collapse: false },
                { position: 'left', header: 'Versions hierarchy', width: 300, resize: true, body: 'left', gutter: '0px 5px', collapse: true, collapseSize: 50, scroll: true, animate: true },
                { position: 'center', body: 'center', /*header: 'SCMF actions', */ scroll: true}
            ]
        });
        layout.on('render', function() {
            layout.getUnitByPosition('left').on('close', function() {
                closeLeft();
            });
        });
        layout.render();
        Event.on('tLeft', 'click', function(ev) {
            Event.stopEvent(ev);
            layout.getUnitByPosition('left').toggle();
        });
        Event.on('tRight', 'click', function(ev) {
            Event.stopEvent(ev);
            layout.getUnitByPosition('right').toggle();
        });
        Event.on('padRight', 'click', function(ev) {
            Event.stopEvent(ev);
            var pad = prompt('CSS gutter to apply: ("2px" or "2px 4px" or any combination of the 4 sides)', layout.getUnitByPosition('right').get('gutter'));
            layout.getUnitByPosition('right').set('gutter', pad);
        });
        var closeLeft = function() {
            var a = document.createElement('a');
            a.href = '#';
            a.innerHTML = 'Add Left Unit';
            Dom.get('closeLeft').parentNode.appendChild(a);

            Dom.setStyle('tLeft', 'display', 'none');
            Dom.setStyle('closeLeft', 'display', 'none');
            Event.on(a, 'click', function(ev) {
                Event.stopEvent(ev);
                Dom.setStyle('tLeft', 'display', 'inline');
                Dom.setStyle('closeLeft', 'display', 'inline');
                a.parentNode.removeChild(a);
                layout.addUnit(layout.get('units')[3]);
                layout.getUnitByPosition('left').on('close', function() {
                    closeLeft();
                });
            });
        };
        Event.on('closeLeft', 'click', function(ev) {
            Event.stopEvent(ev);
            layout.getUnitByPosition('left').close();
        });
    });
})();