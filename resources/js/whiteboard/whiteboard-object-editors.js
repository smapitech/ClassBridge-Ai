import * as fabric from 'fabric';

function clone(value) {
    try {
        return JSON.parse(JSON.stringify(value ?? null));
    } catch {
        return value ?? null;
    }
}

function dashArrayToStrokeStyle(dashArray) {
    if (!Array.isArray(dashArray) || !dashArray.length) {
        return 'solid';
    }

    const signature = dashArray.map((value) => Math.round(Number(value) || 0)).join(',');
    if (signature === '16,10') {
        return 'dashed';
    }

    if (signature === '4,6') {
        return 'dotted';
    }

    if (signature === '16,6,4,6' || signature === '12,6,3,6') {
        return 'dashdot';
    }

    return 'custom';
}

function clamp(value, min, max, fallback = min) {
    const number = Number(value);
    if (Number.isNaN(number)) {
        return fallback;
    }

    return Math.min(max, Math.max(min, number));
}

function getObjectCenter(object) {
    const bounds = object?.getBoundingRect?.(true, true);
    if (!bounds) {
        return {
            x: Number(object?.left || 0),
            y: Number(object?.top || 0),
        };
    }

    return {
        x: bounds.left + bounds.width / 2,
        y: bounds.top + bounds.height / 2,
    };
}

export function getWhiteboardObjectKind(object) {
    if (!object) {
        return 'object';
    }

    return String(object.kind || object.type || 'object');
}

export function normalizeTableMatrix(source, rows, columns) {
    const totalRows = clamp(rows, 1, 20, 2);
    const totalColumns = clamp(columns, 1, 20, 2);
    const matrix = [];
    const input = Array.isArray(source) ? source : [];

    for (let rowIndex = 0; rowIndex < totalRows; rowIndex += 1) {
        const row = Array.isArray(input[rowIndex]) ? input[rowIndex] : [];
        const nextRow = [];

        for (let columnIndex = 0; columnIndex < totalColumns; columnIndex += 1) {
            nextRow.push(String(row[columnIndex] ?? '').trim());
        }

        matrix.push(nextRow);
    }

    return matrix;
}

export function parseTableMatrix(text, rows = 2, columns = 2) {
    const lines = String(text ?? '')
        .split(/\r?\n/)
        .map((line) => line.trimEnd());

    const matrix = [];

    for (let rowIndex = 0; rowIndex < rows; rowIndex += 1) {
        const line = lines[rowIndex] || '';
        const cells = line.split('\t').map((cell) => cell.trim());
        const nextRow = [];

        for (let columnIndex = 0; columnIndex < columns; columnIndex += 1) {
            nextRow.push(String(cells[columnIndex] ?? '').trim());
        }

        matrix.push(nextRow);
    }

    return matrix;
}

export function serializeTableMatrix(matrix) {
    return (Array.isArray(matrix) ? matrix : [])
        .map((row) => (Array.isArray(row) ? row : []).join('\t'))
        .join('\n');
}

function buildTableConfig(options = {}) {
    const rows = clamp(options.rows ?? options.tableConfig?.rows, 1, 20, 2);
    const columns = clamp(options.columns ?? options.tableConfig?.columns, 1, 20, 2);
    const cellWidth = clamp(options.cellWidth ?? options.tableConfig?.cellWidth, 60, 280, 120);
    const cellHeight = clamp(options.cellHeight ?? options.tableConfig?.cellHeight, 36, 180, 60);
    const cellPadding = clamp(options.cellPadding ?? options.tableConfig?.cellPadding, 0, 36, 12);
    const borderWidth = clamp(options.borderWidth ?? options.tableConfig?.borderWidth, 1, 12, 2);
    const stroke = String(options.stroke ?? options.tableConfig?.stroke ?? '#cbd5e1');
    const fill = String(options.fill ?? options.tableConfig?.fill ?? '#ffffff');
    const textColor = String(options.textColor ?? options.tableConfig?.textColor ?? '#0f172a');
    const fontSize = clamp(options.fontSize ?? options.tableConfig?.fontSize, 10, 40, 15);
    const fontFamily = String(options.fontFamily ?? options.tableConfig?.fontFamily ?? 'Instrument Sans, ui-sans-serif, system-ui, sans-serif');
    const textAlign = String(options.textAlign ?? options.tableConfig?.textAlign ?? 'center');
    const rawCells = options.cells ?? options.tableConfig?.cells ?? [];
    const cells = normalizeTableMatrix(rawCells, rows, columns);

    return {
        rows,
        columns,
        cellWidth,
        cellHeight,
        cellPadding,
        borderWidth,
        stroke,
        fill,
        textColor,
        fontSize,
        fontFamily,
        textAlign,
        cells,
    };
}

export function createWhiteboardTableObject(point, options = {}) {
    const config = buildTableConfig(options);
    const totalWidth = config.columns * config.cellWidth;
    const totalHeight = config.rows * config.cellHeight;
    const objects = [];

    const background = new fabric.Rect({
        width: totalWidth,
        height: totalHeight,
        rx: clamp(options.rx ?? 16, 0, 48, 16),
        ry: clamp(options.ry ?? 16, 0, 48, 16),
        fill: config.fill,
        stroke: config.stroke,
        strokeWidth: config.borderWidth,
        originX: 'center',
        originY: 'center',
        selectable: false,
        evented: false,
    });
    objects.push(background);

    for (let column = 1; column < config.columns; column += 1) {
        objects.push(new fabric.Line([
            -totalWidth / 2 + column * config.cellWidth,
            -totalHeight / 2,
            -totalWidth / 2 + column * config.cellWidth,
            totalHeight / 2,
        ], {
            stroke: config.stroke,
            strokeWidth: config.borderWidth,
            originX: 'center',
            originY: 'center',
            selectable: false,
            evented: false,
        }));
    }

    for (let row = 1; row < config.rows; row += 1) {
        objects.push(new fabric.Line([
            -totalWidth / 2,
            -totalHeight / 2 + row * config.cellHeight,
            totalWidth / 2,
            -totalHeight / 2 + row * config.cellHeight,
        ], {
            stroke: config.stroke,
            strokeWidth: config.borderWidth,
            originX: 'center',
            originY: 'center',
            selectable: false,
            evented: false,
        }));
    }

    config.cells.forEach((row, rowIndex) => {
        row.forEach((cellText, columnIndex) => {
            objects.push(new fabric.Textbox(cellText || '', {
                width: Math.max(config.cellWidth - (config.cellPadding * 2), 20),
                height: Math.max(config.cellHeight - (config.cellPadding * 2), 20),
                left: -totalWidth / 2 + (columnIndex * config.cellWidth) + (config.cellWidth / 2),
                top: -totalHeight / 2 + (rowIndex * config.cellHeight) + (config.cellHeight / 2),
                originX: 'center',
                originY: 'center',
                fill: config.textColor,
                fontSize: config.fontSize,
                fontFamily: config.fontFamily,
                textAlign: config.textAlign,
                editable: true,
                selectable: true,
                evented: true,
                transparentCorners: false,
                cornerStyle: 'circle',
                cornerColor: '#0f172a',
                cornerStrokeColor: '#ffffff',
                padding: config.cellPadding / 2,
                kind: 'table-cell',
                layerName: `Cell ${rowIndex + 1}-${columnIndex + 1}`,
                tableRow: rowIndex,
                tableColumn: columnIndex,
            }));
        });
    });

    const table = new fabric.Group(objects, {
        left: point.x,
        top: point.y,
        originX: 'center',
        originY: 'center',
        subTargetCheck: true,
        interactive: true,
    });

    table.set({
        kind: 'table',
        layerName: 'Table',
        tableConfig: config,
    });

    return table;
}

export function createWhiteboardEquationObject(point, options = {}) {
    const config = {
        text: String(options.text ?? options.equationConfig?.text ?? 'x + 3 = 7'),
        fontSize: clamp(options.fontSize ?? options.equationConfig?.fontSize, 10, 72, 28),
        fontFamily: String(options.fontFamily ?? options.equationConfig?.fontFamily ?? 'Georgia, serif'),
        fill: String(options.fill ?? options.equationConfig?.fill ?? '#6b21a8'),
        stroke: String(options.stroke ?? options.equationConfig?.stroke ?? '#a855f7'),
        borderWidth: clamp(options.borderWidth ?? options.equationConfig?.borderWidth, 1, 10, 2),
        paddingX: clamp(options.paddingX ?? options.equationConfig?.paddingX, 6, 48, 24),
        paddingY: clamp(options.paddingY ?? options.equationConfig?.paddingY, 6, 48, 18),
        fontWeight: String(options.fontWeight ?? options.equationConfig?.fontWeight ?? '700'),
        fontStyle: String(options.fontStyle ?? options.equationConfig?.fontStyle ?? 'normal'),
        underline: Boolean(options.underline ?? options.equationConfig?.underline ?? false),
        textAlign: String(options.textAlign ?? options.equationConfig?.textAlign ?? 'center'),
        fillBackground: String(options.fillBackground ?? options.equationConfig?.fillBackground ?? '#faf5ff'),
        kind: 'equation',
    };

    const text = new fabric.Textbox(config.text, {
        width: Math.max(240, Math.min(520, config.text.length * 18)),
        fontSize: config.fontSize,
        fontFamily: config.fontFamily,
        fill: config.fill,
        fontWeight: config.fontWeight,
        fontStyle: config.fontStyle,
        underline: config.underline,
        textAlign: config.textAlign,
        lineHeight: Number(config.lineHeight || 1.3),
        originX: 'center',
        originY: 'center',
        editable: true,
        selectable: true,
        evented: true,
        kind: 'equation-text',
        layerName: 'Equation text',
    });

    const background = new fabric.Rect({
        width: text.width + (config.paddingX * 2),
        height: Math.max(120, text.height + (config.paddingY * 2)),
        rx: 20,
        ry: 20,
        fill: config.fillBackground,
        stroke: config.stroke,
        strokeWidth: config.borderWidth,
        originX: 'center',
        originY: 'center',
        selectable: false,
        evented: false,
    });

    const group = new fabric.Group([background, text], {
        left: point.x,
        top: point.y,
        originX: 'center',
        originY: 'center',
        subTargetCheck: true,
        interactive: true,
    });

    group.set({
        kind: 'equation',
        layerName: 'Equation',
        equationConfig: config,
    });

    return group;
}

export function extractWhiteboardObjectState(object) {
    const bounds = object?.getBoundingRect?.(true, true) || {
        left: Number(object?.left || 0),
        top: Number(object?.top || 0),
        width: Number(object?.width || 0) * Number(object?.scaleX || 1),
        height: Number(object?.height || 0) * Number(object?.scaleY || 1),
    };

    const state = {
        kind: getWhiteboardObjectKind(object),
        geometry: {
            left: Number(bounds.left || object?.left || 0),
            top: Number(bounds.top || object?.top || 0),
            width: Math.max(1, Number(bounds.width || object?.width || 0)),
            height: Math.max(1, Number(bounds.height || object?.height || 0)),
            angle: Number(object?.angle || 0),
            opacity: Math.round((object?.opacity ?? 1) * 100),
            scaleX: Number(object?.scaleX || 1),
            scaleY: Number(object?.scaleY || 1),
        },
        style: {
            stroke: object?.stroke || '#0f172a',
            fill: object?.fill || '#ffffff',
            strokeWidth: Number(object?.strokeWidth || 1),
            strokeDashArray: clone(object?.strokeDashArray || []),
            borderStyle: dashArrayToStrokeStyle(object?.strokeDashArray || []),
            fontSize: Number(object?.fontSize || 18),
            fontFamily: String(object?.fontFamily || 'Instrument Sans, ui-sans-serif, system-ui, sans-serif'),
            fontWeight: String(object?.fontWeight || '600'),
            fontStyle: String(object?.fontStyle || 'normal'),
            underline: Boolean(object?.underline || false),
            textAlign: String(object?.textAlign || 'left'),
            lineHeight: Number(object?.lineHeight || 1.3),
        },
        text: String(object?.text || ''),
        tableConfig: clone(object?.tableConfig || {}),
        equationConfig: clone(object?.equationConfig || {}),
    };

    if (object?.kind === 'equation') {
        const background = object._objects?.[0];
        const label = object._objects?.find((item) => item?.kind === 'equation-text' || item?.type === 'textbox' || item?.type === 'i-text');
        state.text = String(label?.text ?? state.equationConfig?.text ?? object?.text ?? '');
        state.style.fontSize = Number(label?.fontSize || state.style.fontSize || 28);
        state.style.fontFamily = String(label?.fontFamily || state.style.fontFamily);
        state.style.fontWeight = String(label?.fontWeight || state.style.fontWeight);
        state.style.fontStyle = String(label?.fontStyle || state.style.fontStyle);
        state.style.underline = Boolean(label?.underline ?? state.style.underline);
        state.style.textAlign = String(label?.textAlign || state.style.textAlign);
        state.style.lineHeight = Number(label?.lineHeight || state.style.lineHeight || 1.3);
        state.style.fill = String(label?.fill || state.style.fill || '#6b21a8');
        state.style.stroke = String(background?.stroke || state.style.stroke || '#a855f7');
        state.style.strokeWidth = Number(background?.strokeWidth || state.style.strokeWidth || 2);
        state.style.strokeDashArray = clone(background?.strokeDashArray || state.style.strokeDashArray || []);
        state.style.borderStyle = dashArrayToStrokeStyle(state.style.strokeDashArray);
    }

    if (!['table', 'equation'].includes(object?.kind) && Array.isArray(object?._objects) && object._objects.length) {
        const backgroundRect = object._objects.find((item) => item?.type === 'rect');
        if (backgroundRect) {
            state.style.fill = String(backgroundRect.fill || state.style.fill || '#ffffff');
            state.style.stroke = String(backgroundRect.stroke || state.style.stroke || '#0f172a');
            state.style.strokeWidth = Number(backgroundRect.strokeWidth || state.style.strokeWidth || 2);
            state.style.strokeDashArray = clone(backgroundRect.strokeDashArray || state.style.strokeDashArray || []);
            state.style.borderStyle = dashArrayToStrokeStyle(state.style.strokeDashArray);
        }

        const textChild = object._objects.find((item) => item?.type === 'textbox' || item?.type === 'i-text');
        if (textChild) {
            state.text = String(textChild.text ?? state.text ?? '');
            state.style.fontSize = Number(textChild.fontSize || state.style.fontSize);
            state.style.fontFamily = String(textChild.fontFamily || state.style.fontFamily);
            state.style.fontWeight = String(textChild.fontWeight || state.style.fontWeight);
            state.style.fontStyle = String(textChild.fontStyle || state.style.fontStyle);
            state.style.underline = Boolean(textChild.underline ?? state.style.underline);
            state.style.textAlign = String(textChild.textAlign || state.style.textAlign);
            state.style.lineHeight = Number(textChild.lineHeight || state.style.lineHeight || 1.3);
        }
    }

    if (object?.kind === 'table') {
        const config = buildTableConfig(object?.tableConfig || {});
        const cells = [];
        for (let rowIndex = 0; rowIndex < config.rows; rowIndex += 1) {
            const row = [];
            for (let columnIndex = 0; columnIndex < config.columns; columnIndex += 1) {
                const cell = object._objects?.find((item) => Number(item?.tableRow) === rowIndex && Number(item?.tableColumn) === columnIndex);
                row.push(String(cell?.text || config.cells?.[rowIndex]?.[columnIndex] || ''));
            }
            cells.push(row);
        }

        state.tableConfig = {
            ...config,
            cells,
        };
    }

    return state;
}

export function updateWhiteboardEquationObject(object, nextConfig = {}) {
    if (!object) {
        return null;
    }

    const config = {
        ...(clone(object.equationConfig) || {}),
        ...clone(nextConfig),
    };

    object.set({
        equationConfig: config,
    });

    const label = object._objects?.find((item) => item?.kind === 'equation-text' || item?.type === 'textbox' || item?.type === 'i-text');
    if (label) {
        const labelWidth = Math.max(240, Math.min(520, String(config.text ?? '').length * 18));
        label.set({
            text: String(config.text ?? ''),
            width: labelWidth,
            fontSize: clamp(config.fontSize, 10, 72, Number(label.fontSize || 28)),
            fontFamily: String(config.fontFamily || label.fontFamily || 'Georgia, serif'),
            fill: String(config.fill || label.fill || '#6b21a8'),
            fontWeight: String(config.fontWeight || label.fontWeight || '700'),
            fontStyle: String(config.fontStyle || label.fontStyle || 'normal'),
            underline: Boolean(config.underline ?? label.underline ?? false),
            textAlign: String(config.textAlign || label.textAlign || 'center'),
            lineHeight: Number(config.lineHeight || label.lineHeight || 1.3),
        });
    }

    const background = object._objects?.[0];
    if (background) {
        const labelWidth = Number(label?.width || 240);
        const labelHeight = Number(label?.height || 80);
        background.set({
            fill: String(config.fillBackground || background.fill || '#faf5ff'),
            stroke: String(config.stroke || background.stroke || '#a855f7'),
            strokeWidth: clamp(config.borderWidth, 1, 10, Number(background.strokeWidth || 2)),
            width: Math.max(240, labelWidth + (Number(config.paddingX || 24) * 2)),
            height: Math.max(120, labelHeight + (Number(config.paddingY || 18) * 2)),
        });
    }

    object.setCoords();
    return object;
}

export function rebuildWhiteboardTableObject(existingObject, nextConfig = {}) {
    if (!existingObject) {
        return null;
    }

    const center = getObjectCenter(existingObject);
    const config = {
        ...(clone(existingObject.tableConfig) || {}),
        ...clone(nextConfig),
    };

    const table = createWhiteboardTableObject(center, config);
    table.set({
        left: Number(existingObject.left || center.x),
        top: Number(existingObject.top || center.y),
        angle: Number(existingObject.angle || 0),
        scaleX: Number(existingObject.scaleX || 1),
        scaleY: Number(existingObject.scaleY || 1),
        opacity: existingObject.opacity ?? 1,
        cbLocked: Boolean(existingObject.cbLocked || false),
        whiteboardElementId: existingObject.whiteboardElementId || null,
        pageKey: existingObject.pageKey || null,
        tool: existingObject.tool || 'table',
        layerName: existingObject.layerName || 'Table',
    });
    table.setCoords();
    return table;
}
