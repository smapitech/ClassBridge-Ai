import * as fabric from 'fabric';
import {
    createWhiteboardEquationObject,
    createWhiteboardTableObject,
    extractWhiteboardObjectState,
    getWhiteboardObjectKind,
} from './whiteboard/WhiteboardCanvas';
import {
    parseTableMatrix,
    rebuildWhiteboardTableObject,
    serializeTableMatrix,
    updateWhiteboardEquationObject,
} from './whiteboard/WhiteboardPropertiesPanel';

const CUSTOM_PROPS = [
    'whiteboardElementId',
    'pageKey',
    'kind',
    'tool',
    'layerName',
    'cbLocked',
    'tableConfig',
    'equationConfig',
    'tableRow',
    'tableColumn',
];

const DEFAULT_WHITEBOARD_STATE = {
    active_page: 'page-1',
    zoom: 100,
    viewport: {
        x: 0,
        y: 0,
    },
    settings: {
        board_locked: false,
        follow_teacher_page: true,
        follow_teacher_viewport: false,
        presentation_mode: false,
        allow_learner_page_switch: true,
        allow_learner_page_create: false,
        allow_learner_object_move: true,
        allow_learner_draw: true,
        allow_learner_erase: false,
        allow_learner_comments: true,
        allow_learner_images: false,
    },
    pages: [
        {
            key: 'page-1',
            title: 'Page 1',
            page_number: 1,
            background_type: 'plain_white',
            background_value: '#ffffff',
            thumbnail_path: null,
            is_locked: false,
            settings: {},
            sort_order: 0,
        },
    ],
};

const SHAPE_VARIANTS = new Set([
    'rectangle',
    'rounded_rectangle',
    'circle',
    'ellipse',
    'triangle',
    'diamond',
    'star',
    'speech_bubble',
    'cloud',
]);

const LINE_VARIANTS = new Set([
    'line',
    'arrow',
    'double_arrow',
    'dashed_line',
    'curved_connector',
]);

const INSERT_VARIANTS = new Set([
    ...SHAPE_VARIANTS,
    ...LINE_VARIANTS,
    'text',
    'sticky_note',
    'image',
    'table',
    'equation',
    'comment',
    'template',
]);

function clone(value) {
    try {
        return JSON.parse(JSON.stringify(value ?? null));
    } catch {
        return value ?? null;
    }
}

function clamp(value, min, max, fallback = min) {
    const number = Number(value);

    if (Number.isNaN(number)) {
        return fallback;
    }

    return Math.min(max, Math.max(min, number));
}

function strokeStyleToDashArray(style) {
    const normalized = String(style || 'solid').toLowerCase();

    if (normalized === 'dashed') {
        return [16, 10];
    }

    if (normalized === 'dotted') {
        return [4, 6];
    }

    if (normalized === 'dashdot') {
        return [16, 6, 4, 6];
    }

    if (normalized === 'custom') {
        return [12, 6, 3, 6];
    }

    return null;
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

    return 'solid';
}

function applyStrokeStyle(object, style = 'solid') {
    if (!object) {
        return;
    }

    const dash = strokeStyleToDashArray(style);
    const visited = new Set();

    const applyToItem = (item) => {
        if (!item || visited.has(item)) {
            return;
        }

        visited.add(item);

        if ('strokeDashArray' in item) {
            item.set('strokeDashArray', dash || undefined);
        }

        if (item.kind === 'table' && Array.isArray(item._objects)) {
            item._objects.forEach((child) => {
                if (child && 'strokeDashArray' in child) {
                    child.set('strokeDashArray', dash || undefined);
                }
            });
        }

        if (item.kind === 'equation' && Array.isArray(item._objects)) {
            const background = item._objects[0];
            if (background && 'strokeDashArray' in background) {
                background.set('strokeDashArray', dash || undefined);
            }
        }

        if (Array.isArray(item._objects) && item._objects.length) {
            item._objects.forEach(applyToItem);
        }
    };

    applyToItem(object);
    object.setCoords();
}

function getStrokeStyle(object, fallback = 'solid') {
    if (!object) {
        return fallback;
    }

    const dashArray = object.strokeDashArray
        || object._objects?.[0]?.strokeDashArray
        || object._objects?.find((item) => item?.strokeDashArray)?.strokeDashArray
        || [];

    const style = dashArrayToStrokeStyle(dashArray);
    return style || fallback;
}

function formatClock(value = new Date()) {
    const date = value instanceof Date ? value : new Date(value);

    if (Number.isNaN(date.getTime())) {
        return '';
    }

    return date.toLocaleTimeString([], {
        hour: 'numeric',
        minute: '2-digit',
    });
}

function normalizePages(source) {
    const pages = Array.isArray(source) && source.length
        ? source
        : DEFAULT_WHITEBOARD_STATE.pages;

    const normalized = pages.map((page, index) => {
        const candidate = page && typeof page === 'object' ? page : {};
        const pageNumber = Number.isFinite(Number(candidate.page_number))
            ? Number(candidate.page_number)
            : Number.isFinite(Number(candidate.sort_order))
                ? Number(candidate.sort_order) + 1
                : index + 1;

        return {
            key: String(candidate.key || `page-${index + 1}`),
            title: String(candidate.title || candidate.name || `Page ${index + 1}`),
            page_number: Math.max(1, pageNumber),
            sort_order: Number.isFinite(Number(candidate.sort_order)) ? Number(candidate.sort_order) : index,
            background_type: normalizeBackgroundType(candidate.background_type),
            background_value: candidate.background_value ?? (candidate.background_type === 'custom_colour' ? '#ffffff' : null),
            thumbnail_path: candidate.thumbnail_path ? String(candidate.thumbnail_path) : null,
            is_locked: Boolean(candidate.is_locked),
            settings: candidate.settings && typeof candidate.settings === 'object' ? candidate.settings : {},
        };
    });

    return normalized.length ? normalized : clone(DEFAULT_WHITEBOARD_STATE.pages);
}

function uniquePageKey(existingPages) {
    const taken = new Set(existingPages.map((page) => String(page.key)));
    let counter = existingPages.length + 1;
    let key = `page-${counter}`;

    while (taken.has(key)) {
        counter += 1;
        key = `page-${counter}`;
    }

    return key;
}

function normalizeBackgroundType(type) {
    const value = String(type || '').trim().toLowerCase();
    const known = new Set([
        'plain_white',
        'soft_grey',
        'dark_board',
        'grid',
        'graph_paper',
        'ruled_paper',
        'dotted_paper',
        'custom_colour',
        'uploaded_background',
        'pdf_page',
    ]);

    return known.has(value) ? value : 'plain_white';
}

function isTextObject(object) {
    return Boolean(object && (object.type === 'textbox' || object.type === 'i-text' || object.kind === 'text'));
}

function isShapeTool(tool) {
    return SHAPE_VARIANTS.has(tool);
}

function getObjectLabel(object) {
    if (!object) {
        return 'Object';
    }

    if (object.layerName) {
        return String(object.layerName);
    }

    if (object.kind) {
        return String(object.kind).replaceAll('_', ' ');
    }

    return String(object.type || 'Object').replaceAll('_', ' ');
}

function getBoundingBox(objects) {
    const visible = objects.filter((object) => object && object.visible !== false);

    if (!visible.length) {
        return null;
    }

    const rects = visible.map((object) => object.getBoundingRect(true, true));
    const minLeft = Math.min(...rects.map((rect) => rect.left));
    const minTop = Math.min(...rects.map((rect) => rect.top));
    const maxRight = Math.max(...rects.map((rect) => rect.left + rect.width));
    const maxBottom = Math.max(...rects.map((rect) => rect.top + rect.height));

    return {
        left: minLeft,
        top: minTop,
        width: Math.max(1, maxRight - minLeft),
        height: Math.max(1, maxBottom - minTop),
        right: maxRight,
        bottom: maxBottom,
    };
}

function pathFromPoints(points) {
    const safePoints = Array.isArray(points) && points.length ? points : [{ x: 0, y: 0 }, { x: 0.1, y: 0.1 }];
    const [first, ...rest] = safePoints;
    let commands = `M ${first.x} ${first.y}`;

    rest.forEach((point) => {
        commands += ` L ${point.x} ${point.y}`;
    });

    return commands;
}

function roundRectPath(width, height, radius = 16) {
    const r = Math.min(radius, width / 2, height / 2);
    return [
        `M ${r} 0`,
        `H ${width - r}`,
        `Q ${width} 0 ${width} ${r}`,
        `V ${height - r}`,
        `Q ${width} ${height} ${width - r} ${height}`,
        `H ${r}`,
        `Q 0 ${height} 0 ${height - r}`,
        `V ${r}`,
        `Q 0 0 ${r} 0`,
        'Z',
    ].join(' ');
}

function createStickyNote(point, options = {}) {
    const bg = options.fill || '#fef3c7';
    const stroke = options.stroke || '#f59e0b';
    const text = options.text || 'Sticky note';
    const label = new fabric.Textbox(text, {
        width: 180,
        fontSize: 18,
        fontWeight: 700,
        fill: '#7c2d12',
        textAlign: 'left',
        originX: 'center',
        originY: 'center',
        editable: true,
    });

    const body = new fabric.Rect({
        width: 220,
        height: 160,
        rx: 20,
        ry: 20,
        fill: bg,
        stroke,
        strokeWidth: 2,
        originX: 'center',
        originY: 'center',
    });

    const group = new fabric.Group([body, label], {
        left: point.x,
        top: point.y,
        originX: 'center',
        originY: 'center',
        subTargetCheck: true,
    });

    group.set({
        kind: 'sticky_note',
        layerName: 'Sticky note',
        cbLocked: false,
    });

    return group;
}

function createCommentBubble(point, options = {}) {
    const bg = options.fill || '#eff6ff';
    const stroke = options.stroke || '#38bdf8';
    const text = options.text || 'Teacher comment';
    const body = new fabric.Rect({
        width: 250,
        height: 150,
        rx: 22,
        ry: 22,
        fill: bg,
        stroke,
        strokeWidth: 2,
        originX: 'center',
        originY: 'center',
    });

    const tail = new fabric.Triangle({
        width: 26,
        height: 26,
        fill: bg,
        stroke,
        strokeWidth: 2,
        angle: 180,
        originX: 'center',
        originY: 'center',
        top: 92,
        left: 90,
    });

    const label = new fabric.Textbox(text, {
        width: 200,
        fontSize: 16,
        fontWeight: 600,
        fill: '#0f172a',
        textAlign: 'left',
        originX: 'center',
        originY: 'center',
        editable: true,
    });

    const group = new fabric.Group([body, tail, label], {
        left: point.x,
        top: point.y,
        originX: 'center',
        originY: 'center',
        subTargetCheck: true,
    });

    group.set({
        kind: 'comment',
        layerName: 'Comment',
    });

    return group;
}

function createTemplateCard(point, options = {}) {
    const accent = options.fill || '#dbeafe';
    const stroke = options.stroke || '#60a5fa';
    const title = options.title || 'Template';
    const subtitle = options.subtitle || 'Lesson starter';
    const background = new fabric.Rect({
        width: 260,
        height: 170,
        rx: 24,
        ry: 24,
        fill: '#ffffff',
        stroke,
        strokeWidth: 2,
        originX: 'center',
        originY: 'center',
    });
    const topBand = new fabric.Rect({
        width: 260,
        height: 34,
        rx: 24,
        ry: 24,
        fill: accent,
        stroke: accent,
        strokeWidth: 0,
        originX: 'center',
        originY: 'center',
        top: -68,
    });
    const titleText = new fabric.Textbox(title, {
        width: 220,
        fontSize: 20,
        fontWeight: 800,
        fill: '#0f172a',
        originX: 'center',
        originY: 'center',
        top: -6,
        textAlign: 'center',
    });
    const subtitleText = new fabric.Textbox(subtitle, {
        width: 220,
        fontSize: 14,
        fontWeight: 600,
        fill: '#475569',
        originX: 'center',
        originY: 'center',
        top: 36,
        textAlign: 'center',
    });

    const group = new fabric.Group([background, topBand, titleText, subtitleText], {
        left: point.x,
        top: point.y,
        originX: 'center',
        originY: 'center',
        subTargetCheck: true,
    });

    group.set({
        kind: 'template',
        layerName: title,
    });

    return group;
}

function createTableGrid(point, options = {}) {
    return createWhiteboardTableObject(point, options);
}

function createEquationBlock(point, options = {}) {
    return createWhiteboardEquationObject(point, options);
}

function createArrowGroup(point, options = {}) {
    const line = new fabric.Line([-120, 0, 120, 0], {
        stroke: options.stroke || '#0f172a',
        strokeWidth: options.strokeWidth || 4,
        originX: 'center',
        originY: 'center',
        fill: '',
    });

    const head = new fabric.Triangle({
        width: 22,
        height: 22,
        left: 118,
        top: 0,
        fill: options.stroke || '#0f172a',
        angle: 90,
        originX: 'center',
        originY: 'center',
    });

    const group = new fabric.Group([line, head], {
        left: point.x,
        top: point.y,
        originX: 'center',
        originY: 'center',
        subTargetCheck: true,
    });

    group.set({
        kind: 'arrow',
        layerName: 'Arrow',
    });

    return group;
}

function createShape(kind, point, options = {}) {
    const stroke = options.stroke || '#0f172a';
    const fill = options.fill || 'rgba(255,255,255,0.92)';
    const opacity = clamp(options.opacity ?? 100, 10, 100, 100) / 100;
    const common = {
        left: point.x,
        top: point.y,
        originX: 'center',
        originY: 'center',
        stroke,
        fill,
        opacity,
        strokeWidth: options.strokeWidth || 3,
        transparentCorners: false,
        cornerStyle: 'circle',
        cornerColor: '#0f172a',
        cornerStrokeColor: '#ffffff',
        borderColor: '#0f172a',
    };

    let object = null;

    if (kind === 'rectangle') {
        object = new fabric.Rect({
            ...common,
            width: 220,
            height: 140,
        });
    } else if (kind === 'rounded_rectangle') {
        object = new fabric.Rect({
            ...common,
            width: 220,
            height: 140,
            rx: 18,
            ry: 18,
        });
    } else if (kind === 'circle') {
        object = new fabric.Circle({
            ...common,
            radius: 70,
        });
    } else if (kind === 'ellipse') {
        object = new fabric.Ellipse({
            ...common,
            rx: 110,
            ry: 70,
        });
    } else if (kind === 'triangle') {
        object = new fabric.Triangle({
            ...common,
            width: 170,
            height: 150,
        });
    } else if (kind === 'diamond') {
        object = createDiamondShape(point, options);
    } else if (kind === 'star') {
        object = createStarShape(point, options);
    } else if (kind === 'speech_bubble') {
        object = createSpeechBubbleShape(point, options);
    } else if (kind === 'cloud') {
        object = createCloudShape(point, options);
    }

    if (!object) {
        object = new fabric.Rect({
            ...common,
            width: 220,
            height: 140,
        });
    }

    object.set({
        kind,
        layerName: kind.replaceAll('_', ' '),
    });

    return object;
}

function createLegacyPath(element) {
    const data = element?.data || {};
    const points = Array.isArray(data.points) ? data.points : [];
    const stroke = data.color || '#0f172a';
    const width = data.width || (element?.element_type === 'eraser' ? 18 : 4);
    const path = new fabric.Path(pathFromPoints(points), {
        left: points[0]?.x ?? data.x ?? 0,
        top: points[0]?.y ?? data.y ?? 0,
        stroke: stroke,
        strokeWidth: width,
        fill: '',
        selectable: true,
        evented: true,
        originX: 'left',
        originY: 'top',
        globalCompositeOperation: element?.element_type === 'eraser' ? 'destination-out' : 'source-over',
        transparentCorners: false,
        cornerStyle: 'circle',
        cornerColor: '#0f172a',
        cornerStrokeColor: '#ffffff',
    });

    path.set({
        kind: element?.element_type || 'path',
        layerName: element?.element_type === 'eraser' ? 'Eraser stroke' : 'Freehand stroke',
    });

    return path;
}

async function enlivenFabricObject(payload) {
    return new Promise((resolve) => {
        fabric.util.enlivenObjects([payload], (objects) => resolve(objects?.[0] ?? null));
    });
}

function createDefaultObjectLabel(object) {
    if (isTextObject(object)) {
        return 'Text';
    }

    if (object?.kind === 'sticky_note') {
        return 'Sticky note';
    }

    if (object?.kind === 'template') {
        return 'Template';
    }

    return getObjectLabel(object);
}

function isEditingTextObject(canvas) {
    const active = canvas?.getActiveObject?.();
    return Boolean(active?.isEditing);
}

function normalizeSelectionObjects(objects = []) {
    const selected = Array.isArray(objects) ? objects.filter(Boolean) : [];
    const unique = [];
    const seen = new Set();

    selected.forEach((object) => {
        const key = object?.whiteboardElementId ? `server:${object.whiteboardElementId}` : `client:${object?.__uid || object?.__objectId || object?.id || unique.length}`;
        if (seen.has(key)) {
            return;
        }

        seen.add(key);
        unique.push(object);
    });

    return unique;
}

function getObjectCenter(object) {
    const bounds = object?.getBoundingRect?.(true, true);
    if (!bounds) {
        return { x: Number(object?.left || 0), y: Number(object?.top || 0) };
    }

    return {
        x: bounds.left + bounds.width / 2,
        y: bounds.top + bounds.height / 2,
    };
}

function createPolygon(points, options = {}) {
    const polygon = new fabric.Polygon(points, {
        left: options.left ?? 0,
        top: options.top ?? 0,
        originX: 'center',
        originY: 'center',
        fill: options.fill || 'rgba(255,255,255,0.92)',
        stroke: options.stroke || '#0f172a',
        strokeWidth: options.strokeWidth || 3,
        opacity: options.opacity ?? 1,
        transparentCorners: false,
        cornerStyle: 'circle',
        cornerColor: '#0f172a',
        cornerStrokeColor: '#ffffff',
        objectCaching: false,
    });

    return polygon;
}

function createDiamondShape(point, options = {}) {
    const width = Number(options.width || 200);
    const height = Number(options.height || 140);
    return createPolygon([
        { x: 0, y: -height / 2 },
        { x: width / 2, y: 0 },
        { x: 0, y: height / 2 },
        { x: -width / 2, y: 0 },
    ], {
        ...options,
        left: point.x,
        top: point.y,
    });
}

function createStarShape(point, options = {}) {
    const outerRadius = Number(options.outerRadius || 88);
    const innerRadius = Number(options.innerRadius || 44);
    const points = [];

    for (let index = 0; index < 10; index += 1) {
        const radius = index % 2 === 0 ? outerRadius : innerRadius;
        const angle = (-Math.PI / 2) + (Math.PI / 5) * index;
        points.push({
            x: Math.cos(angle) * radius,
            y: Math.sin(angle) * radius,
        });
    }

    return createPolygon(points, {
        ...options,
        left: point.x,
        top: point.y,
    });
}

function createSpeechBubbleShape(point, options = {}) {
    const bg = options.fill || 'rgba(255,255,255,0.95)';
    const stroke = options.stroke || '#0f172a';
    const body = new fabric.Rect({
        width: 260,
        height: 150,
        rx: 24,
        ry: 24,
        fill: bg,
        stroke,
        strokeWidth: options.strokeWidth || 3,
        originX: 'center',
        originY: 'center',
    });

    const tail = new fabric.Triangle({
        width: 30,
        height: 26,
        fill: bg,
        stroke,
        strokeWidth: options.strokeWidth || 3,
        angle: 180,
        originX: 'center',
        originY: 'center',
        left: 88,
        top: 82,
    });

    const label = new fabric.Textbox(options.text || 'Comment', {
        width: 200,
        fontSize: 18,
        fontWeight: 600,
        fill: '#0f172a',
        textAlign: 'left',
        originX: 'center',
        originY: 'center',
        editable: true,
    });

    const group = new fabric.Group([body, tail, label], {
        left: point.x,
        top: point.y,
        originX: 'center',
        originY: 'center',
        subTargetCheck: true,
    });

    group.set({
        kind: 'speech_bubble',
        layerName: 'Speech bubble',
    });

    return group;
}

function createCloudShape(point, options = {}) {
    const fill = options.fill || 'rgba(255,255,255,0.95)';
    const stroke = options.stroke || '#0f172a';
    const strokeWidth = options.strokeWidth || 3;

    const parts = [
        new fabric.Circle({
            radius: 34,
            left: -42,
            top: -4,
            fill,
            stroke,
            strokeWidth,
            originX: 'center',
            originY: 'center',
        }),
        new fabric.Circle({
            radius: 44,
            left: 0,
            top: -18,
            fill,
            stroke,
            strokeWidth,
            originX: 'center',
            originY: 'center',
        }),
        new fabric.Circle({
            radius: 32,
            left: 48,
            top: -2,
            fill,
            stroke,
            strokeWidth,
            originX: 'center',
            originY: 'center',
        }),
        new fabric.Rect({
            width: 140,
            height: 54,
            left: 0,
            top: 24,
            rx: 18,
            ry: 18,
            fill,
            stroke,
            strokeWidth,
            originX: 'center',
            originY: 'center',
        }),
    ];

    const group = new fabric.Group(parts, {
        left: point.x,
        top: point.y,
        originX: 'center',
        originY: 'center',
        subTargetCheck: true,
    });

    group.set({
        kind: 'cloud',
        layerName: 'Cloud',
    });

    return group;
}

function createLineObject(kind, point, options = {}) {
    const stroke = options.stroke || '#0f172a';
    const strokeWidth = options.strokeWidth || 4;
    const dash = kind === 'dashed_line' ? [14, 10] : null;

    if (kind === 'double_arrow') {
        const line = new fabric.Line([-130, 0, 130, 0], {
            stroke,
            strokeWidth,
            strokeDashArray: dash || undefined,
            originX: 'center',
            originY: 'center',
            fill: '',
        });

        const startHead = new fabric.Triangle({
            width: 22,
            height: 22,
            left: -130,
            top: 0,
            fill: stroke,
            angle: -90,
            originX: 'center',
            originY: 'center',
        });

        const endHead = new fabric.Triangle({
            width: 22,
            height: 22,
            left: 130,
            top: 0,
            fill: stroke,
            angle: 90,
            originX: 'center',
            originY: 'center',
        });

        const group = new fabric.Group([line, startHead, endHead], {
            left: point.x,
            top: point.y,
            originX: 'center',
            originY: 'center',
            subTargetCheck: true,
        });

        group.set({
            kind: 'double_arrow',
            layerName: 'Double arrow',
        });

        return group;
    }

    if (kind === 'arrow') {
        return createArrowGroup(point, {
            stroke,
            strokeWidth,
        });
    }

    if (kind === 'curved_connector') {
        const path = new fabric.Path('M -140 0 Q 0 -80 140 0', {
            left: point.x,
            top: point.y,
            originX: 'center',
            originY: 'center',
            stroke,
            strokeWidth,
            fill: '',
            strokeDashArray: dash || undefined,
            transparentCorners: false,
            cornerStyle: 'circle',
            cornerColor: '#0f172a',
            cornerStrokeColor: '#ffffff',
        });

        path.set({
            kind: 'curved_connector',
            layerName: 'Curved connector',
        });

        return path;
    }

    const line = new fabric.Line([-140, 0, 140, 0], {
        stroke,
        strokeWidth,
        strokeDashArray: dash || undefined,
        originX: 'center',
        originY: 'center',
        fill: '',
        transparentCorners: false,
        cornerStyle: 'circle',
        cornerColor: '#0f172a',
        cornerStrokeColor: '#ffffff',
    });

    line.set({
        kind: kind === 'line' ? 'line' : kind,
        layerName: kind === 'dashed_line' ? 'Dashed line' : 'Line',
    });

    return line;
}

export function createWhiteboardFoundation(options = {}) {
    const root = options.root || document.querySelector('[data-whiteboard-root]');
    if (!root) {
        return null;
    }

    const canvasEl = options.canvas || root.querySelector('[data-whiteboard-canvas]');
    const canvasContainer = options.canvasContainer || root.querySelector('[data-whiteboard-canvas-container]');
    const pointerLayer = options.pointerLayer || root.querySelector('#pointers-layer');
    const propertiesPanel = root.querySelector('[data-whiteboard-properties]');
    const emptyState = root.querySelector('[data-whiteboard-empty-state]');
    const shapesMenu = root.querySelector('[data-whiteboard-shapes-menu]');
    const linesMenu = root.querySelector('[data-whiteboard-lines-menu]');
    const moreMenu = root.querySelector('[data-whiteboard-more-menu]');
    const pagesList = root.querySelector('[data-whiteboard-pages-list]');
    const layersList = root.querySelector('[data-whiteboard-layers-list]');
    const templatesList = root.querySelector('[data-whiteboard-templates-list]');
    const commentsList = root.querySelector('[data-whiteboard-comments-list]');
    const activityList = root.querySelector('[data-whiteboard-activity-list]');
    const objectLabel = root.querySelector('[data-whiteboard-object-label]');
    const objectKind = root.querySelector('[data-whiteboard-object-kind]');
    const objectLockButton = root.querySelector('[data-whiteboard-object-lock]');
    const propertySections = [...root.querySelectorAll('[data-whiteboard-property-section]')];
    const propertyFields = [...root.querySelectorAll('[data-whiteboard-prop-field]')];
    const propertyFieldMap = new Map(propertyFields.map((field) => [field.dataset.whiteboardPropField, field]));
    const connectionLabels = [...root.querySelectorAll('[data-whiteboard-connection-state]')];
    const autosaveLabels = [...root.querySelectorAll('[data-whiteboard-autosave-state]')];
    const zoomLabels = [...root.querySelectorAll('[data-whiteboard-zoom-label]')];
    const currentPageLabels = [...root.querySelectorAll('[data-whiteboard-current-page]')];
    const toolButtons = [...root.querySelectorAll('[data-whiteboard-tool]')];
    const actionButtons = [...root.querySelectorAll('[data-whiteboard-action]')];
    const pageButtons = [...root.querySelectorAll('[data-whiteboard-page]')];
    const tabButtons = [...root.querySelectorAll('[data-whiteboard-tab]')];
    const tabPanels = [...root.querySelectorAll('[data-whiteboard-tab-panel]')];
    const colorInputs = [...root.querySelectorAll('[data-whiteboard-prop="stroke"]')];
    const fillInputs = [...root.querySelectorAll('[data-whiteboard-prop="fill"]')];
    const widthInputs = [...root.querySelectorAll('[data-whiteboard-prop="strokeWidth"]')];
    const opacityInputs = [...root.querySelectorAll('[data-whiteboard-prop="opacity"]')];
    const fontSizeInputs = [...root.querySelectorAll('[data-whiteboard-prop="fontSize"]')];
    const textStyleButtons = [...root.querySelectorAll('[data-whiteboard-text-style]')];
    const textAlignButtons = [...root.querySelectorAll('[data-whiteboard-text-align]')];
    const equationInsertButtons = [...root.querySelectorAll('[data-whiteboard-equation-insert]')];
    const tableApplyButton = root.querySelector('[data-whiteboard-action="apply-table"]');
    const templateButtons = [...root.querySelectorAll('[data-whiteboard-template]')];
    const insertShapeButtons = [...root.querySelectorAll('[data-whiteboard-insert-shape]')];
    const insertLineButtons = [...root.querySelectorAll('[data-whiteboard-insert-line]')];
    const pageBackgroundTypeSelect = root.querySelector('[data-whiteboard-page-background-type]');
    const pageBackgroundValueInput = root.querySelector('[data-whiteboard-page-background-value]');
    const snapshotsList = root.querySelector('[data-whiteboard-snapshots-list]');

    if (!canvasEl || !canvasContainer) {
        return null;
    }

    const state = {
        sessionId: options.sessionId || '',
        userId: Number(options.userId || 0),
        isTeacher: Boolean(options.isTeacher),
        activeTool: 'select',
        activeShape: 'rectangle',
        color: '#0f172a',
        fill: '#ffffff',
        strokeWidth: 4,
        opacity: 100,
        fontSize: 22,
        fontFamily: 'Instrument Sans, ui-sans-serif, system-ui, sans-serif',
        fontWeight: '600',
        fontStyle: 'normal',
        underline: false,
        textAlign: 'left',
        lineHeight: 1.4,
        rightPanelTab: 'pages',
        rightPanelOpen: options.rightPanelOpen !== false,
        activePage: 'page-1',
        zoom: 100,
        viewport: { x: 0, y: 0 },
        settings: clone(options.whiteboardState?.settings || options.initialState?.settings || DEFAULT_WHITEBOARD_STATE.settings),
        pages: normalizePages(options.whiteboardState?.pages || options.initialState?.pages || DEFAULT_WHITEBOARD_STATE.pages),
        activity: [],
        connectionState: 'Online',
        autosaveState: 'Saved',
        isRestoring: false,
        isSyncing: false,
        isDrawing: false,
        isPanning: false,
        isErasing: false,
        panStart: null,
        lastPointerSent: 0,
        selectedObjects: [],
        serverElements: new Map(),
        snapshots: [],
        history: [],
        historyIndex: -1,
        suppressHistory: false,
        imagePicker: null,
        clipboardObjects: null,
        pageMenuKey: null,
        draggingPageKey: null,
        spacePressed: false,
        activeLineVariant: 'line',
        pointerPressure: 1,
        pinching: false,
        pinchStartDistance: 0,
        pinchStartZoom: 1,
        pinchStartCenter: { x: 0, y: 0 },
        pinchTouches: [],
    };

    const canvas = new fabric.Canvas(canvasEl, {
        preserveObjectStacking: true,
        selection: true,
        stopContextMenu: true,
        fireRightClick: true,
        allowTouchScrolling: false,
    });

    canvas.selectionColor = 'rgba(15, 23, 42, 0.08)';
    canvas.selectionBorderColor = '#0f172a';
    canvas.freeDrawingCursor = 'crosshair';
    canvas.hoverCursor = 'default';

    function emitActivity(message) {
        const entry = {
            id: `${Date.now()}-${Math.random().toString(36).slice(2)}`,
            message: String(message || ''),
            created_at: new Date().toISOString(),
        };

        state.activity.unshift(entry);
        state.activity = state.activity.slice(0, 24);

        if (typeof options.onActivity === 'function') {
            options.onActivity(entry.message, entry);
        }

        renderActivityList();
    }

    function setConnectionState(text) {
        state.connectionState = String(text || 'Online');
        connectionLabels.forEach((label) => {
            label.textContent = state.connectionState;
        });
    }

    function setAutosaveState(text) {
        state.autosaveState = String(text || 'Saved');
        autosaveLabels.forEach((label) => {
            label.textContent = state.autosaveState;
        });
    }

    function setZoomLabel(value) {
        const label = `${Math.round(value * 100)}%`;
        state.zoom = Math.round(value * 100);
        zoomLabels.forEach((item) => {
            item.textContent = label;
        });
    }

    function setCurrentPageLabel(pageKey) {
        const page = state.pages.find((item) => item.key === pageKey);
        currentPageLabels.forEach((label) => {
            label.textContent = page?.title || page?.name || 'Page';
        });
    }

    function getActivePage() {
        return state.pages.find((page) => page.key === state.activePage) || state.pages[0] || clone(DEFAULT_WHITEBOARD_STATE.pages[0]);
    }

    function getPageByKey(pageKey) {
        return state.pages.find((page) => String(page.key) === String(pageKey)) || null;
    }

    function updatePageInState(pageKey, updater) {
        const index = state.pages.findIndex((page) => String(page.key) === String(pageKey));
        if (index < 0) {
            return null;
        }

        const nextPages = clone(state.pages);
        const nextPage = typeof updater === 'function'
            ? updater({ ...nextPages[index] }) || nextPages[index]
            : { ...nextPages[index], ...(updater || {}) };

        nextPages[index] = {
            ...nextPages[index],
            ...nextPage,
        };
        state.pages = normalizePages(nextPages);
        updatePagesList();

        return getPageByKey(pageKey);
    }

    function capturePageThumbnail(pageKey = state.activePage) {
        const page = getPageByKey(pageKey);
        if (!page || !canvas || typeof canvas.toDataURL !== 'function') {
            return null;
        }

        try {
            const thumbnail = canvas.toDataURL({
                format: 'png',
                multiplier: 0.22,
                enableRetinaScaling: false,
            });
            page.thumbnail_path = thumbnail;
            return thumbnail;
        } catch {
            return null;
        }
    }

    function pageBackgroundColor(page) {
        const type = normalizeBackgroundType(page?.background_type);
        const value = String(page?.background_value || '').trim();

        if (type === 'soft_grey') {
            return value || '#f8fafc';
        }

        if (type === 'dark_board') {
            return value || '#0f172a';
        }

        if (type === 'custom_colour') {
            return value || '#ffffff';
        }

        return value || '#ffffff';
    }

    function createPatternCanvas(page) {
        const patternCanvas = document.createElement('canvas');
        const size = 48;
        patternCanvas.width = size;
        patternCanvas.height = size;
        const context = patternCanvas.getContext('2d');
        if (!context) {
            return patternCanvas;
        }

        const type = normalizeBackgroundType(page?.background_type);
        const base = pageBackgroundColor(page);
        const accent = type === 'dark_board' ? 'rgba(255,255,255,0.18)' : 'rgba(15, 23, 42, 0.08)';
        const dotAccent = type === 'dark_board' ? 'rgba(255,255,255,0.18)' : 'rgba(15, 23, 42, 0.16)';

        context.clearRect(0, 0, size, size);
        context.fillStyle = base;
        context.fillRect(0, 0, size, size);

        if (type === 'grid' || type === 'graph_paper') {
            context.strokeStyle = accent;
            context.lineWidth = 1;
            for (let i = 0; i <= size; i += 16) {
                context.beginPath();
                context.moveTo(i + 0.5, 0);
                context.lineTo(i + 0.5, size);
                context.stroke();
                context.beginPath();
                context.moveTo(0, i + 0.5);
                context.lineTo(size, i + 0.5);
                context.stroke();
            }
            if (type === 'graph_paper') {
                context.strokeStyle = type === 'dark_board' ? 'rgba(255,255,255,0.32)' : 'rgba(15, 23, 42, 0.16)';
                context.lineWidth = 1.5;
                context.beginPath();
                context.moveTo(size / 2 + 0.5, 0);
                context.lineTo(size / 2 + 0.5, size);
                context.moveTo(0, size / 2 + 0.5);
                context.lineTo(size, size / 2 + 0.5);
                context.stroke();
            }
        } else if (type === 'ruled_paper') {
            context.strokeStyle = accent;
            context.lineWidth = 1;
            for (let i = 8; i <= size; i += 16) {
                context.beginPath();
                context.moveTo(0, i + 0.5);
                context.lineTo(size, i + 0.5);
                context.stroke();
            }
        } else if (type === 'dotted_paper') {
            context.fillStyle = dotAccent;
            for (let x = 4; x < size; x += 12) {
                for (let y = 4; y < size; y += 12) {
                    context.beginPath();
                    context.arc(x, y, 1.2, 0, Math.PI * 2);
                    context.fill();
                }
            }
        }

        return patternCanvas;
    }

    async function applyPageBackground(page = getActivePage()) {
        if (!canvas || !page) {
            return;
        }

        const type = normalizeBackgroundType(page.background_type);
        const value = String(page.background_value || '').trim();

        canvas.backgroundImage = null;
        canvas.overlayImage = null;

        if (type === 'uploaded_background' && value) {
            await new Promise((resolve) => {
                fabric.Image.fromURL(value, (image) => {
                    if (!image) {
                        resolve(null);
                        return;
                    }

                    const scaleX = canvas.getWidth() / Math.max(image.width || 1, 1);
                    const scaleY = canvas.getHeight() / Math.max(image.height || 1, 1);
                    image.set({
                        originX: 'left',
                        originY: 'top',
                        left: 0,
                        top: 0,
                        selectable: false,
                        evented: false,
                        excludeFromExport: false,
                        scaleX,
                        scaleY,
                    });
                    canvas.backgroundColor = null;
                    canvas.backgroundImage = image;
                    canvas.requestRenderAll();
                    resolve(image);
                }, { crossOrigin: 'anonymous' });
            });
            return;
        }

        const fills = {
            plain_white: '#ffffff',
            soft_grey: value || '#f8fafc',
            dark_board: value || '#0f172a',
            custom_colour: value || '#ffffff',
        };

        if (['grid', 'graph_paper', 'ruled_paper', 'dotted_paper'].includes(type)) {
            canvas.backgroundColor = new fabric.Pattern({
                source: createPatternCanvas(page),
                repeat: 'repeat',
            });
        } else {
            canvas.backgroundColor = fills[type] || '#ffffff';
        }

        canvas.requestRenderAll();
    }

    function schedulePageThumbnailCapture(pageKey = state.activePage) {
        window.requestAnimationFrame(() => {
            capturePageThumbnail(pageKey);
        });
    }

    function findInsertionIndexForPage(pageKey) {
        const page = getPageByKey(pageKey);
        if (!page) {
            return state.pages.length;
        }

        return state.pages.findIndex((item) => String(item.key) === String(pageKey));
    }

    function isVisibleForActivePage(object) {
        return String(object?.pageKey || getObjectPageKey(object)) === state.activePage;
    }

    function getObjectPageKey(object) {
        return String(object?.pageKey || object?.whiteboardElementId?.pageKey || object?.data?.page_key || state.activePage);
    }

    function setObjectMeta(object, meta = {}) {
        object.set({
            whiteboardElementId: meta.whiteboardElementId ?? object.whiteboardElementId ?? null,
            pageKey: meta.pageKey ?? object.pageKey ?? state.activePage,
            kind: meta.kind ?? object.kind ?? object.type ?? 'object',
            tool: meta.tool ?? object.tool ?? state.activeTool,
            layerName: meta.layerName ?? object.layerName ?? createDefaultObjectLabel(object),
            cbLocked: Boolean(meta.cbLocked ?? object.cbLocked ?? false),
        });

        object.setCoords();
    }

    function getCanvasPayload(object) {
        return {
            action: 'upsert',
            id: object.whiteboardElementId || null,
            element_type: object.kind || object.type || 'object',
            data: {
                page_key: object.pageKey || state.activePage,
                kind: object.kind || object.type || 'object',
                tool: object.tool || state.activeTool,
                layer_name: object.layerName || createDefaultObjectLabel(object),
                fabric: object.toObject(CUSTOM_PROPS),
            },
        };
    }

    function setCanvasSize() {
        if (!canvasContainer) {
            return;
        }

        const rect = canvasContainer.getBoundingClientRect();
        const width = Math.max(320, Math.floor(rect.width));
        const height = Math.max(480, Math.floor(rect.height));

        canvas.setWidth(width);
        canvas.setHeight(height);
        canvas.calcOffset();
        canvas.requestRenderAll();
    }

    function renderEmptyState() {
        if (!emptyState) {
            return;
        }

        const hasVisibleObjects = canvas.getObjects().some((object) => object.visible !== false && object.pageKey === state.activePage);
        emptyState.classList.toggle('hidden', hasVisibleObjects);
        emptyState.classList.toggle('flex', !hasVisibleObjects);
    }

    function updateToolButtons() {
        toolButtons.forEach((button) => {
            const tool = button.dataset.whiteboardTool;
            const active = tool === state.activeTool
                || (tool === 'shapes' && SHAPE_VARIANTS.has(state.activeTool))
                || (tool === 'line' && LINE_VARIANTS.has(state.activeTool))
                || (tool === 'arrow' && state.activeTool === 'arrow')
                || (tool === 'templates' && state.activeTool === 'templates');

            button.classList.toggle('bg-slate-950', active);
            button.classList.toggle('text-white', active);
            button.classList.toggle('border-slate-950', active);
            button.classList.toggle('bg-slate-50', !active);
            button.classList.toggle('text-slate-600', !active);
            button.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
    }

    function updateTabButtons() {
        tabButtons.forEach((button) => {
            const active = button.dataset.whiteboardTab === state.rightPanelTab;
            button.classList.toggle('cb-ide-tab-active', active);
            button.classList.toggle('cb-ide-tab-inactive', !active);
            button.setAttribute('aria-pressed', active ? 'true' : 'false');
        });

        tabPanels.forEach((panel) => {
            panel.classList.toggle('hidden', panel.dataset.whiteboardTabPanel !== state.rightPanelTab);
        });
    }

    function updatePanelVisibility() {
        const panel = root.querySelector('[data-whiteboard-right-panel]');
        if (!panel) {
            return;
        }

        panel.classList.toggle('hidden', !state.rightPanelOpen);
        root.dataset.rightPanelOpen = state.rightPanelOpen ? 'true' : 'false';
    }

    function updatePageButtons() {
        if (!pagesList) {
            return;
        }

        pagesList.querySelectorAll('[data-whiteboard-page-card]').forEach((button) => {
            const active = button.dataset.whiteboardPage === state.activePage;
            button.classList.toggle('border-slate-950', active);
            button.classList.toggle('bg-slate-950', active);
            button.classList.toggle('text-white', active);
            button.classList.toggle('shadow-sm', active);
            button.classList.toggle('border-slate-200', !active);
            button.classList.toggle('bg-white', !active);
            button.classList.toggle('text-slate-600', !active);
        });
    }

    function setInspectorFieldValue(name, value) {
        const field = propertyFieldMap.get(name);
        if (!field) {
            return;
        }

        const nextValue = value ?? '';
        if (String(field.value ?? '') === String(nextValue ?? '')) {
            return;
        }

        field.value = String(nextValue);
    }

    function getInspectorFieldValue(name, fallback = '') {
        return propertyFieldMap.get(name)?.value ?? fallback;
    }

    function togglePropertySections(kind) {
        const textVisible = ['text', 'equation', 'sticky_note', 'comment', 'template', 'table-cell'].includes(kind);
        const tableVisible = kind === 'table';
        const equationVisible = kind === 'equation';

        propertySections.forEach((section) => {
            const sectionKind = section.dataset.whiteboardPropertySection;
            const visible = (sectionKind === 'text' && textVisible)
                || (sectionKind === 'table' && tableVisible)
                || (sectionKind === 'equation' && equationVisible);
            section.classList.toggle('hidden', !visible);
        });
    }

    function resizeObjectToBounds(object, targetWidth, targetHeight) {
        if (!object) {
            return;
        }

        const bounds = object.getBoundingRect(true, true);
        const nextWidth = Math.max(1, Number(targetWidth || bounds.width || 1));
        const nextHeight = Math.max(1, Number(targetHeight || bounds.height || 1));
        const scaleX = nextWidth / Math.max(bounds.width || 1, 1);
        const scaleY = nextHeight / Math.max(bounds.height || 1, 1);

        if (isTextObject(object) && object.kind !== 'table' && object.kind !== 'equation') {
            object.set({
                width: nextWidth,
                scaleX: 1,
                scaleY: 1,
            });
        } else {
            object.set({
                scaleX: (object.scaleX || 1) * scaleX,
                scaleY: (object.scaleY || 1) * scaleY,
            });
        }

        object.setCoords();
    }

    function getTextLikeTargets(object) {
        if (!object) {
            return [];
        }

        if (object.kind === 'table') {
            return [];
        }

        if (isTextObject(object)) {
            return [object];
        }

        return (object._objects || []).filter((child) => isTextObject(child) || child?.kind === 'equation-text');
    }

    function applyTextValue(object, value) {
        const targets = getTextLikeTargets(object);
        if (!targets.length) {
            return false;
        }

        targets.forEach((target) => {
            target.set('text', String(value ?? ''));
        });

        if (object.kind === 'equation') {
            updateWhiteboardEquationObject(object, {
                text: String(value ?? ''),
            });
        }

        return true;
    }

    function replaceCanvasObject(originalObject, nextObject, label = null) {
        if (!originalObject || !nextObject) {
            return null;
        }

        const index = canvas.getObjects().indexOf(originalObject);
        const wasActive = getSelectedObject() === originalObject;

        state.suppressHistory = true;
        canvas.remove(originalObject);
        canvas.insertAt(nextObject, Math.max(index, 0), false);
        state.suppressHistory = false;

        if (wasActive) {
            canvas.setActiveObject(nextObject);
        }

        nextObject.setCoords();
        canvas.requestRenderAll();
        renderEmptyState();
        updateLayersList();
        updatePropertiesVisibility(nextObject);

        if (label) {
            captureHistory(label);
            emitActivity(label.endsWith('.') ? label : `${label}.`);
        }

        return nextObject;
    }

    function applyInspectorChange(field, value) {
        const object = getSelectedObject();
        if (!object) {
            if (field === 'stroke') {
                setColor(value);
                return;
            }

            if (field === 'fill') {
                setFill(value);
                return;
            }

            if (field === 'strokeWidth') {
                setStrokeWidth(Number(value));
                return;
            }

            if (field === 'opacity') {
                setOpacity(Number(value));
                return;
            }

            if (field === 'fontSize') {
                setFontSize(Number(value));
                return;
            }

            if (field === 'fontFamily') {
                state.fontFamily = String(value || state.fontFamily);
                return;
            }

            if (field === 'fontWeight') {
                state.fontWeight = String(value || state.fontWeight);
                return;
            }

            if (field === 'lineHeight') {
                state.lineHeight = clamp(Number(value), 1, 3, state.lineHeight);
                return;
            }

            return;
        }

        const kind = getWhiteboardObjectKind(object);
        const numericValue = Number(value);
        const geometryFields = new Set(['left', 'top', 'width', 'height', 'angle']);

        if (geometryFields.has(field)) {
            if (field === 'left' || field === 'top' || field === 'angle') {
                object.set(field, Number.isNaN(numericValue) ? 0 : numericValue);
            } else {
                resizeObjectToBounds(
                    object,
                    field === 'width' ? numericValue : undefined,
                    field === 'height' ? numericValue : undefined,
                );
            }

            object.setCoords();
            canvas.requestRenderAll();
            updateLayersList();
            updatePropertiesVisibility(object);
            captureHistory(`Changed ${field}`);
            syncObject(object).catch((error) => console.error(error));
            return;
        }

        if (field === 'borderStyle') {
            applyStrokeStyle(object, String(value || 'solid'));
            canvas.requestRenderAll();
            updateLayersList();
            updatePropertiesVisibility(object);
            captureHistory('Changed border style');
            syncObject(object).catch((error) => console.error(error));
            return;
        }

        if (kind === 'equation' && ['stroke', 'fill', 'strokeWidth', 'equationStroke', 'equationBorderWidth', 'equationFontWeight', 'equationFontStyle', 'equationTextAlign', 'equationLineHeight', 'equationPaddingX', 'equationPaddingY'].includes(field)) {
            updateWhiteboardEquationObject(object, {
                stroke: field === 'stroke' || field === 'equationStroke' ? String(value ?? '') : undefined,
                fill: field === 'fill' || field === 'equationFill' ? String(value ?? '') : undefined,
                borderWidth: field === 'strokeWidth' || field === 'equationBorderWidth' ? Number(value) : undefined,
                fontWeight: field === 'fontWeight' || field === 'equationFontWeight' ? String(value ?? '') : undefined,
                fontStyle: field === 'fontStyle' || field === 'equationFontStyle' ? String(value ?? '') : undefined,
                textAlign: field === 'equationTextAlign' ? String(value ?? '') : undefined,
                lineHeight: field === 'lineHeight' || field === 'equationLineHeight' ? Number(value) : undefined,
                paddingX: field === 'equationPaddingX' ? Number(value) : undefined,
                paddingY: field === 'equationPaddingY' ? Number(value) : undefined,
            });
            object.setCoords();
            canvas.requestRenderAll();
            updatePropertiesVisibility(object);
            captureHistory(`Changed ${field}`);
            syncObject(object).catch((error) => console.error(error));
            return;
        }

        if (kind === 'table' && ['stroke', 'fill', 'strokeWidth', 'tableStroke', 'tableFill', 'tableTextColor', 'tableTextAlign', 'tableFontSize', 'tableFontFamily', 'tableBorderWidth', 'tableCellPadding'].includes(field)) {
            applyTableInspector();
            return;
        }

        if (['stroke', 'fill', 'strokeWidth', 'opacity'].includes(field)) {
            if (field === 'opacity') {
                object.set('opacity', clamp(numericValue, 10, 100, 100) / 100);
            } else if (field === 'strokeWidth') {
                object.set(field, clamp(numericValue, 1, 40, state.strokeWidth));
            } else {
                object.set(field, value);
            }

            object.setCoords();
            canvas.requestRenderAll();
            updatePropertiesVisibility(object);
            captureHistory(`Changed ${field}`);
            syncObject(object).catch((error) => console.error(error));
            return;
        }

        if (field === 'fontFamily' || field === 'fontSize' || field === 'fontWeight' || field === 'fontStyle' || field === 'lineHeight') {
            if (kind === 'equation') {
                updateWhiteboardEquationObject(object, {
                    [field]: field === 'fontSize' || field === 'lineHeight' ? Number(value) : value,
                });
            } else {
                const payload = {};
                payload[field] = field === 'fontSize' || field === 'lineHeight' ? Number(value) : value;
                const textTargets = getTextLikeTargets(object);
                if (textTargets.length) {
                    textTargets.forEach((target) => target.set(payload));
                } else {
                    object.set(payload);
                }
            }

            object.setCoords();
            canvas.requestRenderAll();
            updatePropertiesVisibility(object);
            captureHistory(`Changed ${field}`);
            syncObject(object).catch((error) => console.error(error));
            return;
        }

        if (field === 'text') {
            if (applyTextValue(object, value)) {
                object.setCoords();
                canvas.requestRenderAll();
                updateLayersList();
                updatePropertiesVisibility(object);
                captureHistory('Changed text');
                syncObject(object).catch((error) => console.error(error));
            }
            return;
        }

        if (field === 'equationText' || field === 'equationFontFamily' || field === 'equationFontSize' || field === 'equationFill' || field === 'equationBackground' || field === 'equationStroke' || field === 'equationBorderWidth' || field === 'equationFontWeight' || field === 'equationFontStyle' || field === 'equationTextAlign' || field === 'equationLineHeight' || field === 'equationPaddingX' || field === 'equationPaddingY') {
            updateWhiteboardEquationObject(object, {
                text: field === 'equationText' ? String(value ?? '') : undefined,
                fontFamily: field === 'equationFontFamily' ? String(value ?? '') : undefined,
                fontSize: field === 'equationFontSize' ? Number(value) : undefined,
                fill: field === 'equationFill' ? String(value ?? '') : undefined,
                fillBackground: field === 'equationBackground' ? String(value ?? '') : undefined,
                stroke: field === 'equationStroke' ? String(value ?? '') : undefined,
                borderWidth: field === 'equationBorderWidth' ? Number(value) : undefined,
                fontWeight: field === 'equationFontWeight' ? String(value ?? '') : undefined,
                fontStyle: field === 'equationFontStyle' ? String(value ?? '') : undefined,
                textAlign: field === 'equationTextAlign' ? String(value ?? '') : undefined,
                lineHeight: field === 'equationLineHeight' ? Number(value) : undefined,
                paddingX: field === 'equationPaddingX' ? Number(value) : undefined,
                paddingY: field === 'equationPaddingY' ? Number(value) : undefined,
            });
            object.setCoords();
            canvas.requestRenderAll();
            updateLayersList();
            updatePropertiesVisibility(object);
            captureHistory(`Changed ${field}`);
            syncObject(object).catch((error) => console.error(error));
            return;
        }

        if (field.startsWith('table')) {
            if (field === 'tableCells') {
                // Table cell text is applied by the dedicated button so users can edit multiple fields at once.
                return;
            }

            applyTableInspector();
            return;
        }
    }

    function applyTableInspector() {
        const object = getSelectedObject();
        if (!object || object.kind !== 'table') {
            return;
        }

        const rows = clamp(getInspectorFieldValue('tableRows', 2), 1, 12, 2);
        const columns = clamp(getInspectorFieldValue('tableColumns', 2), 1, 12, 2);
        const cellPadding = clamp(getInspectorFieldValue('tableCellPadding', 12), 0, 32, 12);
        const borderWidth = clamp(getInspectorFieldValue('tableBorderWidth', 2), 1, 10, 2);
        const cells = parseTableMatrix(getInspectorFieldValue('tableCells', ''), rows, columns);
        const fill = String(getInspectorFieldValue('tableFill', object.tableConfig?.fill || '#ffffff') || '#ffffff');
        const stroke = String(getInspectorFieldValue('tableStroke', object.tableConfig?.stroke || '#cbd5e1') || '#cbd5e1');
        const textColor = String(getInspectorFieldValue('tableTextColor', object.tableConfig?.textColor || '#0f172a') || '#0f172a');
        const textAlign = String(getInspectorFieldValue('tableTextAlign', object.tableConfig?.textAlign || 'center') || 'center');
        const fontSize = clamp(getInspectorFieldValue('tableFontSize', object.tableConfig?.fontSize || 15), 10, 40, 15);
        const fontFamily = String(getInspectorFieldValue('tableFontFamily', object.tableConfig?.fontFamily || 'Instrument Sans, ui-sans-serif, system-ui, sans-serif') || 'Instrument Sans, ui-sans-serif, system-ui, sans-serif');

        const replacement = rebuildWhiteboardTableObject(object, {
            rows,
            columns,
            cellPadding,
            borderWidth,
            cells,
            fill,
            stroke,
            textColor,
            textAlign,
            fontSize,
            fontFamily,
        });

        if (!replacement) {
            return;
        }

        replacement.set({
            whiteboardElementId: object.whiteboardElementId || null,
            pageKey: object.pageKey || state.activePage,
            tool: object.tool || 'table',
            cbLocked: Boolean(object.cbLocked || false),
        });

        replaceCanvasObject(object, replacement, 'Updated table layout');
        syncObject(replacement).catch((error) => console.error(error));
    }

    function insertEquationSymbol(symbol) {
        const field = getSelectedObject()?.kind === 'equation' ? propertyFieldMap.get('equationText') : null;
        if (!field) {
            return;
        }

        const current = String(field.value || '');
        const next = `${current}${symbol}`;
        field.value = next;
        applyInspectorChange('equationText', next);
    }

    function updatePropertiesVisibility(object = getSelectedObject()) {
        if (!propertiesPanel) {
            return;
        }

        const shouldShow = Boolean(object) || !['select', 'hand'].includes(state.activeTool);
        propertiesPanel.classList.toggle('hidden', !shouldShow);

        if (!shouldShow) {
            togglePropertySections(null);
            return;
        }

        const inspector = object
            ? extractWhiteboardObjectState(object)
            : {
                geometry: {
                    width: 0,
                    height: 0,
                    angle: 0,
                    opacity: state.opacity / 100,
                },
                style: {
                    stroke: state.color,
                    fill: state.fill,
                    strokeWidth: state.strokeWidth,
                    fontFamily: state.fontFamily,
                    fontWeight: state.fontWeight,
                    lineHeight: state.lineHeight,
                },
                text: '',
                tableConfig: {
                    rows: 2,
                    columns: 2,
                    cellPadding: 12,
                    borderWidth: 2,
                    cells: [],
                },
                equationConfig: {
                    fontFamily: state.fontFamily,
                    fontSize: state.fontSize,
                    fill: '#6b21a8',
                    fillBackground: '#faf5ff',
                },
            };
        const kind = object ? getWhiteboardObjectKind(object) : state.activeTool;
        const fontSize = Number(inspector.style.fontSize || state.fontSize);

        setInspectorFieldValue('left', Math.round(Number(object.left || 0)));
        setInspectorFieldValue('top', Math.round(Number(object.top || 0)));
        setInspectorFieldValue('width', Math.round(inspector.geometry.width));
        setInspectorFieldValue('height', Math.round(inspector.geometry.height));
        setInspectorFieldValue('angle', Math.round(inspector.geometry.angle));
        setInspectorFieldValue('fontFamily', inspector.style.fontFamily);
        setInspectorFieldValue('fontSize', fontSize);
        setInspectorFieldValue('fontWeight', inspector.style.fontWeight);
        setInspectorFieldValue('fontStyle', inspector.style.fontStyle || 'normal');
        setInspectorFieldValue('lineHeight', inspector.style.lineHeight || 1.4);
        setInspectorFieldValue('borderStyle', getStrokeStyle(object, inspector.style.borderStyle || 'solid'));
        setInspectorFieldValue('text', inspector.text || '');
        setInspectorFieldValue('tableRows', inspector.tableConfig?.rows || 2);
        setInspectorFieldValue('tableColumns', inspector.tableConfig?.columns || 2);
        setInspectorFieldValue('tableCellPadding', inspector.tableConfig?.cellPadding || 12);
        setInspectorFieldValue('tableBorderWidth', inspector.tableConfig?.borderWidth || 2);
        setInspectorFieldValue('tableCells', serializeTableMatrix(inspector.tableConfig?.cells || []));
        setInspectorFieldValue('tableFill', inspector.tableConfig?.fill || '#ffffff');
        setInspectorFieldValue('tableStroke', inspector.tableConfig?.stroke || '#cbd5e1');
        setInspectorFieldValue('tableTextColor', inspector.tableConfig?.textColor || '#0f172a');
        setInspectorFieldValue('tableTextAlign', inspector.tableConfig?.textAlign || 'center');
        setInspectorFieldValue('tableFontSize', inspector.tableConfig?.fontSize || fontSize);
        setInspectorFieldValue('tableFontFamily', inspector.tableConfig?.fontFamily || inspector.style.fontFamily);
        setInspectorFieldValue('equationText', inspector.text || '');
        setInspectorFieldValue('equationFontFamily', inspector.equationConfig?.fontFamily || inspector.style.fontFamily);
        setInspectorFieldValue('equationFontSize', inspector.equationConfig?.fontSize || fontSize);
        setInspectorFieldValue('equationFill', inspector.equationConfig?.fill || inspector.style.fill || '#6b21a8');
        setInspectorFieldValue('equationBackground', inspector.equationConfig?.fillBackground || '#faf5ff');
        setInspectorFieldValue('equationStroke', inspector.equationConfig?.stroke || inspector.style.stroke || '#a855f7');
        setInspectorFieldValue('equationBorderWidth', inspector.equationConfig?.borderWidth || inspector.style.strokeWidth || 2);
        setInspectorFieldValue('equationFontWeight', inspector.equationConfig?.fontWeight || inspector.style.fontWeight || '700');
        setInspectorFieldValue('equationFontStyle', inspector.equationConfig?.fontStyle || inspector.style.fontStyle || 'normal');
        setInspectorFieldValue('equationTextAlign', inspector.equationConfig?.textAlign || inspector.style.textAlign || 'center');
        setInspectorFieldValue('equationLineHeight', inspector.equationConfig?.lineHeight || inspector.style.lineHeight || 1.3);
        setInspectorFieldValue('equationPaddingX', inspector.equationConfig?.paddingX || 24);
        setInspectorFieldValue('equationPaddingY', inspector.equationConfig?.paddingY || 18);

        const equationPreview = root.querySelector('[data-whiteboard-equation-preview]');
        if (equationPreview) {
            equationPreview.textContent = String(inspector.text || 'x + 3 = 7');
            equationPreview.style.fontFamily = inspector.equationConfig?.fontFamily || inspector.style.fontFamily || 'Georgia, serif';
            equationPreview.style.fontSize = `${Math.max(12, Number(inspector.equationConfig?.fontSize || fontSize || 28))}px`;
            equationPreview.style.fontWeight = String(inspector.equationConfig?.fontWeight || inspector.style.fontWeight || '700');
            equationPreview.style.fontStyle = String(inspector.equationConfig?.fontStyle || inspector.style.fontStyle || 'normal');
            equationPreview.style.textAlign = String(inspector.equationConfig?.textAlign || inspector.style.textAlign || 'center');
            equationPreview.style.lineHeight = String(inspector.equationConfig?.lineHeight || inspector.style.lineHeight || 1.3);
            equationPreview.style.color = String(inspector.equationConfig?.fill || inspector.style.fill || '#6b21a8');
            equationPreview.style.background = String(inspector.equationConfig?.fillBackground || '#faf5ff');
            equationPreview.style.borderColor = String(inspector.equationConfig?.stroke || inspector.style.stroke || '#a855f7');
            equationPreview.style.borderWidth = `${Number(inspector.equationConfig?.borderWidth || inspector.style.strokeWidth || 2)}px`;
            equationPreview.style.borderStyle = inspector.style.borderStyle || 'solid';
            equationPreview.style.padding = `${Number(inspector.equationConfig?.paddingY || 18)}px ${Number(inspector.equationConfig?.paddingX || 24)}px`;
        }

        colorInputs.forEach((input) => {
            input.value = inspector.style.stroke || state.color;
        });

        fillInputs.forEach((input) => {
            input.value = typeof inspector.style.fill === 'string' && inspector.style.fill.startsWith('#')
                ? inspector.style.fill
                : '#ffffff';
        });

        widthInputs.forEach((input) => {
            input.value = String(inspector.style.strokeWidth || state.strokeWidth);
        });

        opacityInputs.forEach((input) => {
            input.value = String(inspector.geometry.opacity || 100);
        });

        fontSizeInputs.forEach((input) => {
            input.value = String(fontSize);
        });

        if (objectLabel) {
            objectLabel.textContent = object ? createDefaultObjectLabel(object) : `${state.activeTool.replaceAll('_', ' ')} tool`;
        }

        if (objectKind) {
            objectKind.textContent = object
                ? kind.replaceAll('_', ' ').replaceAll('-', ' ')
                : 'Tool defaults';
        }

        if (objectLockButton) {
            objectLockButton.textContent = object && object.cbLocked ? 'Unlock' : 'Lock';
            objectLockButton.disabled = !object;
        }

        togglePropertySections(object ? kind : null);
    }

    function updateLayersList() {
        if (!layersList) {
            return;
        }

        const activeObjects = canvas
            .getObjects()
            .filter((object) => isVisibleForActivePage(object))
            .map((object, index) => ({ object, index }))
            .reverse();

        layersList.innerHTML = '';

        if (!activeObjects.length) {
            const empty = document.createElement('div');
            empty.className = 'rounded-2xl border border-dashed border-slate-200 bg-white px-4 py-5 text-sm text-slate-500';
            empty.textContent = 'No objects yet on this page.';
            layersList.appendChild(empty);
            return;
        }

        activeObjects.forEach(({ object, index }) => {
            const row = document.createElement('div');
            row.className = 'flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3';
            row.dataset.whiteboardLayerRow = 'true';
            row.dataset.whiteboardLayerId = String(object.whiteboardElementId || '');

            const left = document.createElement('button');
            left.type = 'button';
            left.className = 'min-w-0 flex-1 text-left';
            left.innerHTML = `
                <div class="truncate text-sm font-semibold text-slate-900">${createDefaultObjectLabel(object)}</div>
                <div class="text-[11px] text-slate-500">${String(object.kind || object.type || 'object').replaceAll('_', ' ')}</div>
            `;
            left.addEventListener('click', () => {
                canvas.setActiveObject(object);
                canvas.requestRenderAll();
                updatePropertiesVisibility(object);
                updateLayersList();
            });

            const controls = document.createElement('div');
            controls.className = 'flex items-center gap-2';

            const toggle = document.createElement('button');
            toggle.type = 'button';
            toggle.className = 'rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-[11px] font-semibold text-slate-600';
            toggle.textContent = object.visible === false ? 'Show' : 'Hide';
            toggle.addEventListener('click', async () => {
                object.visible = object.visible === false;
                object.setCoords();
                canvas.requestRenderAll();
                renderEmptyState();
                updateLayersList();
                await syncObject(object);
                emitActivity(`${object.visible === false ? 'Hid' : 'Showed'} ${createDefaultObjectLabel(object)}.`);
            });

            const deleteButton = document.createElement('button');
            deleteButton.type = 'button';
            deleteButton.className = 'rounded-full border border-rose-200 bg-rose-50 px-3 py-1 text-[11px] font-semibold text-rose-700';
            deleteButton.textContent = 'Delete';
            deleteButton.addEventListener('click', async () => {
                await deleteObject(object);
            });

            controls.append(toggle, deleteButton);
            row.append(left, controls);
            layersList.appendChild(row);
        });
    }

    function pagePreviewStyle(page) {
        const type = normalizeBackgroundType(page?.background_type);
        const value = String(page?.background_value || '').trim();

        if (page?.thumbnail_path) {
            return {
                backgroundImage: `url("${page.thumbnail_path}")`,
                backgroundSize: 'cover',
                backgroundPosition: 'center',
                backgroundColor: '#ffffff',
            };
        }

        if (type === 'dark_board') {
            return {
                backgroundColor: value || '#0f172a',
                backgroundImage: 'linear-gradient(135deg, rgba(255,255,255,0.08), rgba(255,255,255,0))',
            };
        }

        if (type === 'soft_grey' || type === 'plain_white' || type === 'custom_colour') {
            return {
                backgroundColor: value || (type === 'soft_grey' ? '#f8fafc' : '#ffffff'),
            };
        }

        if (type === 'grid' || type === 'graph_paper') {
            return {
                backgroundColor: value || '#ffffff',
                backgroundImage: [
                    'linear-gradient(to right, rgba(15,23,42,0.08) 1px, transparent 1px)',
                    'linear-gradient(to bottom, rgba(15,23,42,0.08) 1px, transparent 1px)',
                ].join(', '),
                backgroundSize: type === 'graph_paper' ? '16px 16px, 16px 16px' : '20px 20px, 20px 20px',
            };
        }

        if (type === 'ruled_paper') {
            return {
                backgroundColor: value || '#ffffff',
                backgroundImage: 'repeating-linear-gradient(to bottom, rgba(15,23,42,0.08) 0, rgba(15,23,42,0.08) 1px, transparent 1px, transparent 22px)',
            };
        }

        if (type === 'dotted_paper') {
            return {
                backgroundColor: value || '#ffffff',
                backgroundImage: 'radial-gradient(circle, rgba(15,23,42,0.18) 1px, transparent 1px)',
                backgroundSize: '12px 12px',
            };
        }

        return {
            backgroundColor: '#ffffff',
        };
    }

    function closePageMenus() {
        state.pageMenuKey = null;
        if (!pagesList) {
            return;
        }

        pagesList.querySelectorAll('[data-whiteboard-page-menu]').forEach((menu) => {
            menu.classList.add('hidden');
        });
    }

    function updatePagesList() {
        if (!pagesList) {
            return;
        }

        pagesList.innerHTML = '';

        state.pages.forEach((page, index) => {
            const active = page.key === state.activePage;
            const card = document.createElement('div');
            card.dataset.whiteboardPageCard = page.key;
            card.dataset.whiteboardPage = page.key;
            card.draggable = true;
            card.className = `group relative overflow-hidden rounded-[1.35rem] border px-3 py-3 text-left transition ${active ? 'border-slate-950 bg-slate-950 text-white shadow-sm' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:text-slate-900'}`;

            const previewRow = document.createElement('div');
            previewRow.className = 'flex items-start gap-3';

            const preview = document.createElement('div');
            preview.className = 'relative h-14 w-20 shrink-0 overflow-hidden rounded-2xl border border-white/70 bg-slate-100 shadow-sm';
            const previewStyle = pagePreviewStyle(page);
            Object.assign(preview.style, previewStyle);
            if (!page.thumbnail_path) {
                const overlay = document.createElement('div');
                overlay.className = 'absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(255,255,255,0.45),_transparent_42%)]';
                preview.appendChild(overlay);
            }

            const body = document.createElement('button');
            body.type = 'button';
            body.className = 'min-w-0 flex-1 text-left';
            body.addEventListener('click', () => {
                setActivePage(page.key, { persist: true, announce: true });
            });

            const titleRow = document.createElement('div');
            titleRow.className = 'flex items-center gap-2';
            const title = document.createElement('span');
            title.className = 'truncate text-sm font-bold';
            title.textContent = page.title || `Page ${index + 1}`;
            const number = document.createElement('span');
            number.className = `rounded-full px-2 py-0.5 text-[10px] font-black uppercase tracking-[0.2em] ${active ? 'bg-white/10 text-white/80' : 'bg-slate-100 text-slate-500'}`;
            number.textContent = `#${page.page_number || index + 1}`;
            titleRow.append(title, number);

            const metaRow = document.createElement('div');
            metaRow.className = 'mt-1 flex items-center gap-2 text-[11px] font-semibold';
            const lockBadge = document.createElement('span');
            lockBadge.className = active ? 'text-white/80' : 'text-slate-500';
            lockBadge.textContent = page.is_locked ? 'Locked' : 'Unlocked';
            const backgroundBadge = document.createElement('span');
            backgroundBadge.className = active ? 'text-white/70' : 'text-slate-400';
            backgroundBadge.textContent = page.background_type ? page.background_type.replaceAll('_', ' ') : 'plain white';
            metaRow.append(lockBadge, backgroundBadge);

            body.append(titleRow, metaRow);

            const handle = document.createElement('button');
            handle.type = 'button';
            handle.className = `grid h-10 w-10 shrink-0 place-items-center rounded-2xl border text-xs font-black ${active ? 'border-white/15 bg-white/10 text-white' : 'border-slate-200 bg-slate-50 text-slate-500'}`;
            handle.title = 'Drag to reorder';
            handle.innerHTML = '<span aria-hidden="true">⋮⋮</span>';
            handle.addEventListener('dragstart', (event) => {
                state.draggingPageKey = page.key;
                event.dataTransfer?.setData('text/plain', page.key);
                event.dataTransfer?.setDragImage?.(handle, 12, 12);
            });

            const moreWrap = document.createElement('div');
            moreWrap.className = 'absolute right-2 top-2';

            const moreButton = document.createElement('button');
            moreButton.type = 'button';
            moreButton.className = `grid h-8 w-8 place-items-center rounded-xl border text-xs font-black ${active ? 'border-white/15 bg-white/10 text-white' : 'border-slate-200 bg-white text-slate-500'}`;
            moreButton.setAttribute('aria-label', 'Page options');
            moreButton.textContent = '⋯';

            const menu = document.createElement('div');
            menu.dataset.whiteboardPageMenu = page.key;
            menu.className = 'absolute right-0 top-10 z-20 hidden w-52 overflow-hidden rounded-2xl border border-slate-200 bg-white p-1 shadow-xl';

            const menuItems = [
                { label: 'Rename page', action: 'rename-page' },
                { label: 'Duplicate page', action: 'duplicate-page' },
                { label: page.is_locked ? 'Unlock page' : 'Lock page', action: page.is_locked ? 'unlock-page' : 'lock-page' },
                { label: 'Change background', action: 'background-page' },
                { label: 'Clear page', action: 'clear-page' },
                { label: 'Delete page', action: 'delete-page', danger: true },
            ];

            menuItems.forEach((item) => {
                const menuButton = document.createElement('button');
                menuButton.type = 'button';
                menuButton.dataset.whiteboardPageAction = item.action;
                menuButton.dataset.whiteboardPageKey = page.key;
                menuButton.className = `w-full rounded-xl px-3 py-2 text-left text-sm font-semibold transition ${item.danger ? 'text-rose-700 hover:bg-rose-50' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'}`;
                menuButton.textContent = item.label;
                menuButton.addEventListener('click', () => {
                    handlePageAction(item.action, page.key);
                });
                menu.appendChild(menuButton);
            });

            moreButton.addEventListener('click', (event) => {
                event.stopPropagation();
                const shouldShow = menu.classList.contains('hidden');
                closePageMenus();
                menu.classList.toggle('hidden', !shouldShow);
                state.pageMenuKey = shouldShow ? page.key : null;
            });

            moreWrap.append(moreButton, menu);

            previewRow.append(preview, body, handle);
            card.append(previewRow, moreWrap);

            card.addEventListener('dragover', (event) => {
                event.preventDefault();
            });

            card.addEventListener('drop', (event) => {
                event.preventDefault();
                const sourceKey = state.draggingPageKey || event.dataTransfer?.getData('text/plain');
                state.draggingPageKey = null;
                if (sourceKey && sourceKey !== page.key) {
                    reorderPages(sourceKey, page.key);
                }
            });

            card.addEventListener('dragend', () => {
                state.draggingPageKey = null;
            });

            pagesList.appendChild(card);
        });

        updatePageButtons();
    }

    function updateCommentsList() {
        if (!commentsList) {
            return;
        }

        if (!commentsList.children.length) {
            commentsList.innerHTML = `
                <div class="rounded-2xl border border-dashed border-slate-200 bg-white px-4 py-5 text-sm text-slate-500">
                    No comments yet.
                </div>
            `;
        }
    }

    function updateActivityList() {
        if (!activityList) {
            return;
        }

        activityList.innerHTML = '';

        if (!state.activity.length) {
            const empty = document.createElement('div');
            empty.className = 'rounded-2xl border border-dashed border-slate-200 bg-white px-4 py-5 text-sm text-slate-500';
            empty.textContent = 'Board is ready.';
            activityList.appendChild(empty);
            return;
        }

        state.activity.slice(0, 12).forEach((entry) => {
            const item = document.createElement('div');
            item.className = 'rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600';
            item.innerHTML = `
                <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">${formatClock(entry.created_at)}</div>
                <div class="mt-1 leading-6">${entry.message}</div>
            `;
            activityList.appendChild(item);
        });
    }

    function updateSnapshotsList() {
        if (!snapshotsList) {
            return;
        }

        snapshotsList.innerHTML = '';

        if (!Array.isArray(state.snapshots) || !state.snapshots.length) {
            const empty = document.createElement('div');
            empty.className = 'rounded-2xl border border-dashed border-slate-200 bg-white px-4 py-5 text-sm text-slate-500';
            empty.textContent = 'No snapshots yet.';
            snapshotsList.appendChild(empty);
            return;
        }

        state.snapshots.slice(0, 10).forEach((snapshot) => {
            const item = document.createElement('div');
            item.className = 'rounded-2xl border border-slate-200 bg-white px-4 py-3';

            const top = document.createElement('div');
            top.className = 'flex items-start justify-between gap-3';

            const stack = document.createElement('div');
            const title = document.createElement('p');
            title.className = 'text-sm font-semibold text-slate-900';
            title.textContent = snapshot.name || snapshot.reason || 'Snapshot';
            const meta = document.createElement('p');
            meta.className = 'mt-1 text-xs text-slate-500';
            meta.textContent = `${snapshot.creator?.display_name || snapshot.creator?.name || 'Teacher'} · ${formatClock(snapshot.created_at)}`;
            stack.append(title, meta);

            const restore = document.createElement('button');
            restore.type = 'button';
            restore.className = 'rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-[11px] font-semibold text-slate-600 hover:border-slate-300 hover:text-slate-900';
            restore.textContent = 'Restore';
            restore.dataset.whiteboardSnapshotId = snapshot.id;
            restore.addEventListener('click', () => {
                if (!options.restoreSnapshot) {
                    return;
                }
                if (!window.confirm(`Restore snapshot "${snapshot.name || 'Snapshot'}"?`)) {
                    return;
                }
                options.restoreSnapshot(snapshot.id).catch((error) => console.error(error));
            });

            top.append(stack, restore);
            item.append(top);
            snapshotsList.appendChild(item);
        });
    }

    function syncPageBackgroundControls() {
        const page = getActivePage();
        if (pageBackgroundTypeSelect) {
            pageBackgroundTypeSelect.value = normalizeBackgroundType(page?.background_type || 'plain_white');
        }
        if (pageBackgroundValueInput) {
            pageBackgroundValueInput.value = String(page?.background_value || '');
        }
    }

    function setCurrentModeText() {
        const modeLabel = root.querySelector('[data-whiteboard-board-title]');
        if (!modeLabel) {
            return;
        }
    }

    function getSelectedObject() {
        const active = canvas.getActiveObject();

        if (!active) {
            return null;
        }

        if (active.type === 'activeSelection' && active._objects?.length) {
            return active._objects[0];
        }

        return active;
    }

    function getSelectedObjects() {
        const active = canvas.getActiveObject();
        if (!active) {
            return [];
        }

        if (active.type === 'activeSelection' && Array.isArray(active._objects)) {
            return active._objects.filter(Boolean);
        }

        return [active];
    }

    function isCanvasTextEditing() {
        return isEditingTextObject(canvas);
    }

    function updateBrushSettings(pressure = 1) {
        if (!canvas.freeDrawingBrush) {
            return;
        }

        const pressureScale = clamp(pressure || 1, 0.25, 1.5, 1);
        const baseWidth = state.activeTool === 'highlighter'
            ? Math.max(state.strokeWidth + 10, 16)
            : (state.activeTool === 'eraser' ? Math.max(state.strokeWidth + 12, 18) : state.strokeWidth);
        const width = Math.max(1, Math.round(baseWidth * pressureScale));

        canvas.freeDrawingBrush.color = state.activeTool === 'highlighter'
            ? 'rgba(250, 204, 21, 0.35)'
            : (state.activeTool === 'eraser' ? 'rgba(0,0,0,1)' : state.color);
        canvas.freeDrawingBrush.width = width;
        canvas.freeDrawingBrush.shadow = state.activeTool === 'highlighter'
            ? new fabric.Shadow({ color: 'rgba(250, 204, 21, 0.35)', blur: 6 })
            : null;
        canvas.freeDrawingBrush.globalCompositeOperation = state.activeTool === 'eraser' ? 'destination-out' : 'source-over';
    }

    function setTool(tool, { quiet = false } = {}) {
        const normalized = String(tool || 'select');
        state.activeTool = normalized;
        if (SHAPE_VARIANTS.has(normalized)) {
            state.activeShape = normalized;
        }
        if (LINE_VARIANTS.has(normalized)) {
            state.activeLineVariant = normalized;
        }

        if (normalized === 'hand') {
            canvas.isDrawingMode = false;
            canvas.selection = false;
            canvas.skipTargetFind = true;
            canvas.defaultCursor = 'grab';
            canvas.hoverCursor = 'grab';
        } else if (normalized === 'pen' || normalized === 'highlighter') {
            canvas.isDrawingMode = true;
            canvas.selection = false;
            canvas.skipTargetFind = true;
            canvas.defaultCursor = 'crosshair';
            canvas.hoverCursor = 'crosshair';
            canvas.freeDrawingBrush = new fabric.PencilBrush(canvas);
            updateBrushSettings(state.pointerPressure || 1);
        } else if (normalized === 'eraser') {
            canvas.isDrawingMode = false;
            canvas.selection = false;
            canvas.skipTargetFind = false;
            canvas.defaultCursor = 'crosshair';
            canvas.hoverCursor = 'crosshair';
        } else {
            canvas.isDrawingMode = false;
            canvas.selection = normalized === 'select';
            canvas.skipTargetFind = normalized !== 'select';
            canvas.defaultCursor = normalized === 'select' ? 'default' : 'crosshair';
            canvas.hoverCursor = normalized === 'select' ? 'move' : 'crosshair';
        }

        updateToolButtons();
        refreshAccess();
        if (!quiet) {
            emitActivity(`Selected ${normalized.replaceAll('_', ' ')} tool.`);
        }
    }

    function setColor(color) {
        state.color = String(color || '#0f172a');
        updateBrushSettings(state.pointerPressure || 1);

        const object = getSelectedObject();
        if (object) {
            if (object.kind === 'equation') {
                updateWhiteboardEquationObject(object, { stroke: state.color });
            } else if (object.kind === 'table') {
                const replacement = rebuildWhiteboardTableObject(object, {
                    ...(object.tableConfig || {}),
                    stroke: state.color,
                });
                if (replacement) {
                    replacement.set({
                        whiteboardElementId: object.whiteboardElementId || null,
                        pageKey: object.pageKey || state.activePage,
                        tool: object.tool || 'table',
                        cbLocked: Boolean(object.cbLocked || false),
                    });
                    replaceCanvasObject(object, replacement, 'Changed stroke color');
                    syncObject(replacement).catch(() => null);
                    return;
                }
            } else {
                object.set('stroke', state.color);
                const backgroundTargets = Array.isArray(object._objects)
                    ? object._objects.filter((child) => child?.type === 'rect' || child?.type === 'path' || child?.type === 'line')
                    : [];
                if (backgroundTargets.length) {
                    backgroundTargets.forEach((child) => {
                        child.set('stroke', state.color);
                    });
                } else {
                    const textTargets = getTextLikeTargets(object);
                    textTargets.forEach((target) => {
                        target.set('fill', state.color);
                    });
                }
            }
            canvas.requestRenderAll();
            captureHistory('Changed stroke color');
            syncObject(object).catch(() => null);
        }
    }

    function setFill(fill) {
        state.fill = String(fill || '#ffffff');
        const object = getSelectedObject();
        if (object) {
            if (object.kind === 'equation') {
                updateWhiteboardEquationObject(object, { fill: state.fill });
            } else if (object.kind === 'table') {
                const replacement = rebuildWhiteboardTableObject(object, {
                    ...(object.tableConfig || {}),
                    fill: state.fill,
                });
                if (replacement) {
                    replacement.set({
                        whiteboardElementId: object.whiteboardElementId || null,
                        pageKey: object.pageKey || state.activePage,
                        tool: object.tool || 'table',
                        cbLocked: Boolean(object.cbLocked || false),
                    });
                    replaceCanvasObject(object, replacement, 'Changed fill color');
                    syncObject(replacement).catch(() => null);
                    return;
                }
            } else {
                object.set('fill', state.fill);
                if (Array.isArray(object._objects) && object._objects.length) {
                    const rectTargets = object._objects.filter((child) => child?.type === 'rect');
                    if (rectTargets.length) {
                        rectTargets.forEach((child) => {
                            child.set('fill', state.fill);
                        });
                    } else {
                        const textTargets = getTextLikeTargets(object);
                        textTargets.forEach((target) => {
                            target.set('fill', state.fill);
                        });
                    }
                }
            }
            canvas.requestRenderAll();
            captureHistory('Changed fill color');
            syncObject(object).catch(() => null);
        }
    }

    function setStrokeWidth(strokeWidth) {
        state.strokeWidth = clamp(strokeWidth, 1, 40, state.strokeWidth);
        updateBrushSettings(state.pointerPressure || 1);

        const object = getSelectedObject();
        if (object) {
            if (object.kind === 'equation') {
                updateWhiteboardEquationObject(object, { borderWidth: state.strokeWidth });
            } else if (object.kind === 'table') {
                const replacement = rebuildWhiteboardTableObject(object, {
                    ...(object.tableConfig || {}),
                    borderWidth: state.strokeWidth,
                });
                if (replacement) {
                    replacement.set({
                        whiteboardElementId: object.whiteboardElementId || null,
                        pageKey: object.pageKey || state.activePage,
                        tool: object.tool || 'table',
                        cbLocked: Boolean(object.cbLocked || false),
                    });
                    replaceCanvasObject(object, replacement, 'Changed stroke width');
                    syncObject(replacement).catch(() => null);
                    return;
                }
            } else {
                object.set('strokeWidth', state.strokeWidth);
                if (Array.isArray(object._objects) && object._objects.length) {
                    object._objects.forEach((child) => {
                        if (child?.type === 'rect' || child?.type === 'line' || child?.type === 'path') {
                            child.set('strokeWidth', state.strokeWidth);
                        }
                    });
                }
            }
            canvas.requestRenderAll();
            captureHistory('Changed stroke width');
            syncObject(object).catch(() => null);
        }
    }

    function setOpacity(opacity) {
        state.opacity = clamp(opacity, 10, 100, state.opacity);
        updateBrushSettings(state.pointerPressure || 1);
        const object = getSelectedObject();
        if (object) {
            object.set('opacity', state.opacity / 100);
            canvas.requestRenderAll();
            captureHistory('Changed opacity');
            syncObject(object).catch(() => null);
        }
    }

    function setFontSize(fontSize) {
        state.fontSize = clamp(fontSize, 8, 120, state.fontSize);
        const object = getSelectedObject();
        if (object) {
            const textTargets = getTextLikeTargets(object);
            if (textTargets.length) {
                textTargets.forEach((target) => {
                    target.set('fontSize', state.fontSize);
                });
            }
            canvas.requestRenderAll();
            captureHistory('Changed font size');
            syncObject(object).catch(() => null);
        }
    }

    function applyTextStyle(style) {
        const object = getSelectedObject();
        if (!object) {
            if (style === 'bold') {
                state.fontWeight = state.fontWeight === '700' ? '600' : '700';
            } else if (style === 'italic') {
                state.fontStyle = state.fontStyle === 'italic' ? 'normal' : 'italic';
            } else if (style === 'underline') {
                state.underline = !state.underline;
            }
            return;
        }

        const targets = getTextLikeTargets(object);
        if (!targets.length) {
            return;
        }

        targets.forEach((target) => {
            if (style === 'bold') {
                target.set('fontWeight', target.fontWeight === 'bold' ? 'normal' : 'bold');
            } else if (style === 'italic') {
                target.set('fontStyle', target.fontStyle === 'italic' ? 'normal' : 'italic');
            } else if (style === 'underline') {
                target.set('underline', !target.underline);
            }
        });

        canvas.requestRenderAll();
        captureHistory(`Changed ${style} text style`);
        syncObject(object).catch(() => null);
    }

    function applyTextAlign(align) {
        const object = getSelectedObject();
        if (!object) {
            state.textAlign = align;
            return;
        }

        const targets = getTextLikeTargets(object);
        if (!targets.length) {
            return;
        }

        targets.forEach((target) => {
            target.set('textAlign', align);
        });
        canvas.requestRenderAll();
        captureHistory(`Changed text alignment to ${align}`);
        syncObject(object).catch(() => null);
    }

    function getCanvasPoint(evt) {
        const pointer = canvas.getPointer(evt.e || evt);
        return {
            x: Number(pointer.x.toFixed(2)),
            y: Number(pointer.y.toFixed(2)),
        };
    }

    async function addObjectToCanvas(object, { select = true, announce = true } = {}) {
        setObjectMeta(object, {
            pageKey: state.activePage,
            kind: object.kind || object.type || 'object',
            tool: state.activeTool,
            layerName: createDefaultObjectLabel(object),
        });

        canvas.add(object);

        if (select) {
            canvas.setActiveObject(object);
        }

        object.visible = isVisibleForActivePage(object);
        object.setCoords();
        canvas.requestRenderAll();
        renderEmptyState();
        updateLayersList();
        updatePropertiesVisibility(object);

        if (announce) {
            emitActivity(`Added ${createDefaultObjectLabel(object)}.`);
        }

        await syncObject(object).catch((error) => {
            console.error(error);
        });

        captureHistory(`Added ${createDefaultObjectLabel(object)}`);

        return object;
    }

    async function createAndAddObject(tool, point, options = {}) {
        let object = null;

        if (tool === 'text') {
            object = new fabric.IText(options.text || 'Type here', {
                left: point.x,
                top: point.y,
                originX: 'left',
                originY: 'top',
                fontSize: options.fontSize || state.fontSize,
                fill: options.fill || state.color,
                fontWeight: options.fontWeight || state.fontWeight || '600',
                fontFamily: options.fontFamily || state.fontFamily || 'Instrument Sans, ui-sans-serif, system-ui, sans-serif',
                fontStyle: options.fontStyle || state.fontStyle || 'normal',
                underline: options.underline ?? state.underline ?? false,
                lineHeight: options.lineHeight || state.lineHeight || 1.4,
                editable: true,
                transparentCorners: false,
                cornerStyle: 'circle',
                cornerColor: '#0f172a',
                cornerStrokeColor: '#ffffff',
                textAlign: options.textAlign || state.textAlign || 'left',
            });
        } else if (tool === 'sticky_note') {
            object = createStickyNote(point, {
                fill: options.fill,
                stroke: options.stroke,
                text: options.text,
            });
        } else if (tool === 'line' || tool === 'arrow' || LINE_VARIANTS.has(tool)) {
            object = createLineObject(tool, point, {
                stroke: options.stroke || state.color,
                strokeWidth: options.strokeWidth || state.strokeWidth,
            });
        } else if (tool === 'table') {
            object = createTableGrid(point, options);
        } else if (tool === 'equation') {
            object = createEquationBlock(point, options);
        } else if (tool === 'comment') {
            object = createCommentBubble(point, options);
        } else if (tool === 'template') {
            object = createTemplateCard(point, options);
        } else if (tool === 'image') {
            const url = options.url || '';
            if (!url) {
                return null;
            }

            object = await new Promise((resolve, reject) => {
                fabric.Image.fromURL(url, (image) => {
                    if (!image) {
                        reject(new Error('Could not load image.'));
                        return;
                    }

                    image.set({
                        left: point.x,
                        top: point.y,
                        originX: 'center',
                        originY: 'center',
                        scaleX: 0.45,
                        scaleY: 0.45,
                        transparentCorners: false,
                        cornerStyle: 'circle',
                        cornerColor: '#0f172a',
                        cornerStrokeColor: '#ffffff',
                    });
                    image.set({ kind: 'image', layerName: options.label || 'Image' });
                    resolve(image);
                }, { crossOrigin: 'anonymous' });
            });
        } else if (tool === 'diamond') {
            object = createDiamondShape(point, options);
        } else if (tool === 'star') {
            object = createStarShape(point, options);
        } else if (tool === 'speech_bubble') {
            object = createSpeechBubbleShape(point, options);
        } else if (tool === 'cloud') {
            object = createCloudShape(point, options);
        } else {
            object = createShape(tool, point, options);
        }

        if (!object) {
            return null;
        }

        return addObjectToCanvas(object, { select: true, announce: true });
    }

    function captureHistory(label = null) {
        if (state.suppressHistory || state.isRestoring) {
            return;
        }

        const snapshot = {
            canvas: canvas.toJSON(CUSTOM_PROPS),
            whiteboardState: getState(),
            label,
        };

        if (state.historyIndex < state.history.length - 1) {
            state.history = state.history.slice(0, state.historyIndex + 1);
        }

        state.history.push(snapshot);
        if (state.history.length > 30) {
            state.history.shift();
        }

        state.historyIndex = state.history.length - 1;
    }

    function getState() {
        return {
            active_page: state.activePage,
            zoom: state.zoom,
            viewport: clone(state.viewport),
            settings: clone(state.settings),
            pages: clone(state.pages),
        };
    }

    function applyState(whiteboardState = {}) {
        state.pages = normalizePages(whiteboardState.pages || state.pages);
        state.activePage = state.pages.find((page) => page.key === whiteboardState.active_page)?.key || state.pages[0]?.key || 'page-1';
        state.settings = {
            ...clone(DEFAULT_WHITEBOARD_STATE.settings || {}),
            ...(whiteboardState.settings || {}),
        };
        state.viewport = {
            x: Number(whiteboardState.viewport?.x || 0),
            y: Number(whiteboardState.viewport?.y || 0),
        };
        const zoom = clamp(whiteboardState.zoom || 100, 20, 300, 100) / 100;
        canvas.setZoom(zoom);
        canvas.absolutePan(new fabric.Point(state.viewport.x, state.viewport.y));
        setZoomLabel(zoom);
        setCurrentPageLabel(state.activePage);
        updatePagesList();
        updateLayersList();
        renderEmptyState();
    }

    function scheduleLayoutSave(reason = 'layout') {
        if (state.layoutTimer) {
            clearTimeout(state.layoutTimer);
        }

        capturePageThumbnail(state.activePage);
        setAutosaveState('Saving...');
        state.layoutTimer = window.setTimeout(async () => {
            try {
                if (typeof options.saveLayout === 'function') {
                    await options.saveLayout(getState(), reason);
                }
                setAutosaveState('Saved');
            } catch (error) {
                console.error(error);
                setAutosaveState('Save failed');
            }
        }, 350);
    }

    async function syncObject(object, action = 'upsert') {
        if (!object) {
            return null;
        }

        if (state.isRestoring) {
            return null;
        }

        setAutosaveState(action === 'delete' ? 'Deleting...' : 'Saving...');
        const payload = getCanvasPayload(object);
        payload.action = action;

        if (action === 'delete') {
            if (!object.whiteboardElementId) {
                setAutosaveState('Saved');
                return { deleted: true };
            }

            if (typeof options.deleteElement === 'function') {
                await options.deleteElement(object.whiteboardElementId);
            }
            state.serverElements.delete(String(object.whiteboardElementId || ''));
            setAutosaveState('Saved');
            return { deleted: true };
        }

        if (typeof options.saveElement !== 'function') {
            setAutosaveState('Saved');
            return null;
        }

        const response = await options.saveElement(payload);
        const element = response?.element || null;
        if (element) {
            object.whiteboardElementId = element.id;
            object.pageKey = element.data?.page_key || object.pageKey || state.activePage;
            object.kind = element.element_type || object.kind || object.type;
            object.layerName = element.data?.layer_name || object.layerName || createDefaultObjectLabel(object);
            state.serverElements.set(String(element.id), element);
        }

        setAutosaveState(response?.saved ? 'Saved' : 'Saved');
        return response;
    }

    async function deleteObject(object) {
        if (!object) {
            return;
        }

        state.suppressHistory = true;
        canvas.remove(object);
        canvas.discardActiveObject();
        state.suppressHistory = false;
        canvas.requestRenderAll();
        renderEmptyState();
        updateLayersList();
        updatePropertiesVisibility(null);
        captureHistory(`Deleted ${createDefaultObjectLabel(object)}`);
        emitActivity(`Deleted ${createDefaultObjectLabel(object)}.`);

        await syncObject(object, 'delete');
    }

    function eraseObjectAtPoint(opt) {
        if (!opt?.e) {
            return;
        }

        const target = canvas.findTarget(opt.e, false);
        if (!target) {
            return;
        }

        const objects = target.type === 'activeSelection'
            ? target.getObjects()
            : [target];

        objects.forEach((object) => {
            if (!object || object.cbLocked) {
                return;
            }

            const key = String(object.whiteboardElementId || object.__uid || object.__objectId || '');
            if (state.erasedKeys?.has(key)) {
                return;
            }

            state.erasedKeys = state.erasedKeys || new Set();
            state.erasedKeys.add(key);
            deleteObject(object).catch((error) => console.error(error));
        });
    }

    async function cloneSelectedObjects({ offset = 24, clearSelection = false } = {}) {
        const selectedObjects = normalizeSelectionObjects(getSelectedObjects());
        if (!selectedObjects.length) {
            return [];
        }

        const clones = [];

        for (const target of selectedObjects) {
            // eslint-disable-next-line no-await-in-loop
            const cloneObject = await new Promise((resolve) => target.clone((cloned) => resolve(cloned), CUSTOM_PROPS));
            cloneObject.set({
                left: (target.left || 0) + offset,
                top: (target.top || 0) + offset,
                whiteboardElementId: null,
                cbLocked: false,
                selectable: true,
                evented: true,
            });
            clones.push(cloneObject);
        }

        if (clearSelection) {
            canvas.discardActiveObject();
        }

        state.suppressHistory = true;
        clones.forEach((cloneObject) => canvas.add(cloneObject));
        state.suppressHistory = false;

        if (clones.length === 1) {
            canvas.setActiveObject(clones[0]);
        } else if (clones.length > 1) {
            canvas.setActiveObject(new fabric.ActiveSelection(clones, { canvas }));
        }

        canvas.requestRenderAll();
        updateLayersList();

        for (const cloneObject of clones) {
            // eslint-disable-next-line no-await-in-loop
            await syncObject(cloneObject, 'upsert');
        }

        captureHistory(offset > 0 ? 'Duplicated selection' : 'Copied selection');
        emitActivity(offset > 0 ? 'Duplicated the selection.' : 'Copied the selection.');
        return clones;
    }

    async function duplicateSelection() {
        return cloneSelectedObjects({ offset: 24, clearSelection: false });
    }

    async function copySelection() {
        const selectedObjects = normalizeSelectionObjects(getSelectedObjects());
        if (!selectedObjects.length) {
            return;
        }

        state.clipboardObjects = selectedObjects.map((object) => object.toObject(CUSTOM_PROPS));
        canvas.discardActiveObject();
        canvas.requestRenderAll();
    }

    async function pasteSelection() {
        if (!Array.isArray(state.clipboardObjects) || !state.clipboardObjects.length) {
            return;
        }

        const objects = await new Promise((resolve) => {
            fabric.util.enlivenObjects(state.clipboardObjects, (items) => resolve(items || []));
        });

        const pasted = [];

        state.suppressHistory = true;
        objects.forEach((object) => {
            if (!object) {
                return;
            }

            object.set({
                left: (object.left || 0) + 28,
                top: (object.top || 0) + 28,
                whiteboardElementId: null,
                pageKey: state.activePage,
                cbLocked: false,
            });
            canvas.add(object);
            pasted.push(object);
        });
        state.suppressHistory = false;

        if (pasted.length === 1) {
            canvas.setActiveObject(pasted[0]);
        } else if (pasted.length > 1) {
            canvas.setActiveObject(new fabric.ActiveSelection(pasted, { canvas }));
        }

        canvas.requestRenderAll();
        updateLayersList();

        for (const object of pasted) {
            // eslint-disable-next-line no-await-in-loop
            await syncObject(object, 'upsert');
        }

        captureHistory('Pasted selection');
        emitActivity('Pasted the clipboard.');
    }

    function selectAllEditableObjects() {
        const objects = canvas
            .getObjects()
            .filter((object) => object.visible !== false && !object.cbLocked && object.pageKey === state.activePage);

        if (!objects.length) {
            return;
        }

        if (objects.length === 1) {
            canvas.setActiveObject(objects[0]);
        } else {
            canvas.setActiveObject(new fabric.ActiveSelection(objects, { canvas }));
        }

        canvas.requestRenderAll();
        updateLayersList();
        updatePropertiesVisibility(getSelectedObject());
    }

    function getSelectedRectangle(objects = getSelectedObjects()) {
        const selected = normalizeSelectionObjects(objects).filter((object) => object && object.visible !== false);

        if (!selected.length) {
            return null;
        }

        const bounds = getBoundingBox(selected);
        if (!bounds) {
            return null;
        }

        return { selected, bounds };
    }

    function alignSelectedObjects(align) {
        const selection = getSelectedRectangle();
        if (!selection || selection.selected.length < 2) {
            return;
        }

        const { selected, bounds } = selection;

        selected.forEach((object) => {
            const objectBounds = object.getBoundingRect(true, true);
            const center = {
                x: objectBounds.left + objectBounds.width / 2,
                y: objectBounds.top + objectBounds.height / 2,
            };

            if (align === 'left') {
                object.left += bounds.left - objectBounds.left;
            } else if (align === 'right') {
                object.left += bounds.right - (objectBounds.left + objectBounds.width);
            } else if (align === 'top') {
                object.top += bounds.top - objectBounds.top;
            } else if (align === 'bottom') {
                object.top += bounds.bottom - (objectBounds.top + objectBounds.height);
            } else if (align === 'center' || align === 'centre') {
                object.left += (bounds.left + bounds.width / 2) - center.x;
            } else if (align === 'middle') {
                object.top += (bounds.top + bounds.height / 2) - center.y;
            }

            object.setCoords();
        });

        canvas.requestRenderAll();
        captureHistory(`Aligned selection ${align}`);
        selected.forEach((object) => syncObject(object).catch((error) => console.error(error)));
        emitActivity(`Aligned selection ${align}.`);
    }

    function distributeSelectedObjects(direction) {
        const selection = getSelectedRectangle();
        if (!selection || selection.selected.length < 3) {
            return;
        }

        const items = selection.selected
            .slice()
            .sort((a, b) => {
                const rectA = a.getBoundingRect(true, true);
                const rectB = b.getBoundingRect(true, true);
                return direction === 'horizontal' ? rectA.left - rectB.left : rectA.top - rectB.top;
            });

        const bounds = getBoundingBox(items);
        if (!bounds) {
            return;
        }

        if (direction === 'horizontal') {
            const totalWidth = items.reduce((sum, object) => sum + object.getBoundingRect(true, true).width, 0);
            const gap = Math.max((bounds.width - totalWidth) / (items.length - 1), 0);
            let cursor = bounds.left;

            items.forEach((object) => {
                const rect = object.getBoundingRect(true, true);
                object.left += cursor - rect.left;
                object.setCoords();
                cursor += rect.width + gap;
            });
        } else {
            const totalHeight = items.reduce((sum, object) => sum + object.getBoundingRect(true, true).height, 0);
            const gap = Math.max((bounds.height - totalHeight) / (items.length - 1), 0);
            let cursor = bounds.top;

            items.forEach((object) => {
                const rect = object.getBoundingRect(true, true);
                object.top += cursor - rect.top;
                object.setCoords();
                cursor += rect.height + gap;
            });
        }

        canvas.requestRenderAll();
        captureHistory(`Distributed selection ${direction}`);
        items.forEach((object) => syncObject(object).catch((error) => console.error(error)));
        emitActivity(`Distributed selection ${direction}.`);
    }

    async function groupSelectedObjects() {
        const selection = getSelectedRectangle();
        if (!selection || selection.selected.length < 2) {
            return;
        }

        const originalIds = selection.selected
            .map((object) => object.whiteboardElementId)
            .filter(Boolean);

        for (const id of originalIds) {
            if (typeof options.deleteElement === 'function') {
                // eslint-disable-next-line no-await-in-loop
                await options.deleteElement(id);
            }
            state.serverElements.delete(String(id));
        }

        selection.selected.forEach((object) => {
            object.whiteboardElementId = null;
            object.cbLocked = false;
        });

        const group = new fabric.ActiveSelection(selection.selected, { canvas });
        canvas.setActiveObject(group);
        canvas.requestRenderAll();

        const grouped = group.toGroup();
        grouped.set({
            kind: 'group',
            layerName: 'Group',
        });
        canvas.setActiveObject(grouped);
        canvas.requestRenderAll();
        updateLayersList();
        captureHistory('Grouped selection');
        await syncAllObjectsToServer();
        emitActivity('Grouped the selection.');
    }

    function ungroupSelectedObjects() {
        const active = canvas.getActiveObject();
        if (!active || active.type !== 'group') {
            return;
        }

        active.toActiveSelection();
        canvas.requestRenderAll();
        updateLayersList();
        captureHistory('Ungrouped selection');
        emitActivity('Ungrouped the selection.');
        syncAllObjectsToServer().catch((error) => console.error(error));
    }

    function resetZoom() {
        canvas.setViewportTransform([1, 0, 0, 1, 0, 0]);
        canvas.setZoom(1);
        state.viewport = { x: 0, y: 0 };
        setZoomLabel(1);
        canvas.requestRenderAll();
        scheduleLayoutSave('reset-zoom');
    }

    async function loadElements(elements = []) {
        const records = Array.isArray(elements) ? elements : [];

        state.isRestoring = true;
        state.history = [];
        state.historyIndex = -1;
        canvas.getObjects().slice().forEach((object) => canvas.remove(object));
        canvas.discardActiveObject();
        state.serverElements.clear();

        for (const record of records) {
            // eslint-disable-next-line no-await-in-loop
            const object = await createObjectFromRecord(record);
            if (!object) {
                continue;
            }

            if (record?.id) {
                object.whiteboardElementId = record.id;
                state.serverElements.set(String(record.id), record);
            }

            object.visible = String(object.pageKey || state.activePage) === state.activePage;
            canvas.add(object);
        }

        state.isRestoring = false;
        canvas.requestRenderAll();
        renderEmptyState();
        updateLayersList();
        updatePagesList();
        captureHistory('Loaded whiteboard');
    }

    async function createObjectFromRecord(record) {
        const data = clone(record?.data || {});
        const fabricPayload = data.fabric || data.object || null;
        const pageKey = String(data.page_key || fabricPayload?.pageKey || state.activePage);
        let object = null;

        if (fabricPayload && fabricPayload.type) {
            object = await enlivenFabricObject(fabricPayload);
        } else if (record?.element_type) {
            object = createLegacyObject(record);
        }

        if (!object) {
            return null;
        }

        setObjectMeta(object, {
            whiteboardElementId: record.id,
            pageKey,
            kind: record.element_type || fabricPayload?.kind || object.type,
            tool: data.tool || fabricPayload?.tool || record.element_type,
            layerName: data.layer_name || fabricPayload?.layerName || createDefaultObjectLabel(object),
        });

        object.visible = pageKey === state.activePage;
        return object;
    }

    function createLegacyObject(record) {
        const data = record?.data || {};
        const type = String(record?.element_type || '').toLowerCase();

        if (type === 'text') {
            const text = new fabric.Textbox(String(data.text || 'Text'), {
                left: Number(data.x || 24),
                top: Number(data.y || 24),
                originX: 'left',
                originY: 'top',
                fontSize: Number(data.fontSize || 18),
                fill: String(data.color || '#0f172a'),
                editable: true,
                fontFamily: 'Instrument Sans, ui-sans-serif, system-ui, sans-serif',
                textAlign: 'left',
            });

            text.set({ kind: 'text', layerName: 'Text' });
            return text;
        }

        if (type === 'shape_rect' || type === 'shape_rectangle') {
            const start = data.start || data.points?.[0] || { x: data.x || 0, y: data.y || 0 };
            const end = data.end || data.points?.[data.points.length - 1] || start;
            const left = Math.min(start.x, end.x);
            const top = Math.min(start.y, end.y);
            const width = Math.max(Math.abs(end.x - start.x), 1);
            const height = Math.max(Math.abs(end.y - start.y), 1);

            const rect = new fabric.Rect({
                left,
                top,
                originX: 'left',
                originY: 'top',
                width,
                height,
                fill: 'rgba(255,255,255,0.85)',
                stroke: String(data.color || '#0f172a'),
                strokeWidth: Number(data.lineWidth || 3),
            });

            rect.set({ kind: 'rectangle', layerName: 'Rectangle' });
            return rect;
        }

        if (type === 'shape_circle' || type === 'circle') {
            const start = data.start || data.points?.[0] || { x: data.x || 0, y: data.y || 0 };
            const end = data.end || data.points?.[data.points.length - 1] || start;
            const left = Math.min(start.x, end.x);
            const top = Math.min(start.y, end.y);
            const width = Math.max(Math.abs(end.x - start.x), 1);
            const height = Math.max(Math.abs(end.y - start.y), 1);

            const ellipse = new fabric.Ellipse({
                left,
                top,
                originX: 'left',
                originY: 'top',
                rx: width / 2,
                ry: height / 2,
                fill: 'rgba(255,255,255,0.85)',
                stroke: String(data.color || '#0f172a'),
                strokeWidth: Number(data.lineWidth || 3),
            });

            ellipse.set({ kind: 'ellipse', layerName: 'Circle' });
            return ellipse;
        }

        return createLegacyPath(record);
    }

    async function removeRemoteElement(id) {
        const object = canvas.getObjects().find((item) => String(item.whiteboardElementId) === String(id));
        if (!object) {
            return;
        }

        state.isRestoring = true;
        canvas.remove(object);
        state.isRestoring = false;
        canvas.requestRenderAll();
        renderEmptyState();
        updateLayersList();
    }

    async function applyRemoteElement(record) {
        if (!record) {
            return;
        }

        const existing = canvas.getObjects().find((item) => String(item.whiteboardElementId) === String(record.id));
        const nextObject = await createObjectFromRecord(record);
        if (!nextObject) {
            return;
        }

        state.isRestoring = true;
        const index = existing ? canvas.getObjects().indexOf(existing) : canvas.getObjects().length;
        if (existing) {
            canvas.remove(existing);
        }
        canvas.insertAt(nextObject, Math.max(index, 0), false);
        state.isRestoring = false;
        state.serverElements.set(String(record.id), record);
        canvas.requestRenderAll();
        renderEmptyState();
        updateLayersList();
        emitActivity(`${record.user_name || 'Someone'} updated the board.`);
    }

    async function clearRemote(pageKey = null) {
        state.isRestoring = true;
        state.history = [];
        state.historyIndex = -1;
        const objects = canvas.getObjects().slice();
        objects.forEach((object) => {
            if (pageKey && String(object.pageKey || state.activePage) !== String(pageKey)) {
                return;
            }
            canvas.remove(object);
        });
        if (!pageKey) {
            state.serverElements.clear();
        } else {
            for (const [id, element] of state.serverElements.entries()) {
                if (String(element?.data?.page_key || state.activePage) === String(pageKey)) {
                    state.serverElements.delete(id);
                }
            }
        }
        state.isRestoring = false;
        canvas.requestRenderAll();
        renderEmptyState();
        updateLayersList();
    }

    function setActivePage(pageKey, { persist = true, announce = false } = {}) {
        if (!pageKey) {
            return;
        }

        const previousPageKey = state.activePage;
        if (previousPageKey) {
            capturePageThumbnail(previousPageKey);
        }

        state.activePage = String(pageKey);
        state.pages = normalizePages(state.pages);

        canvas.getObjects().forEach((object) => {
            object.visible = String(object.pageKey || state.activePage) === state.activePage;
            object.setCoords();
        });

        canvas.discardActiveObject();
        void applyPageBackground(getActivePage()).catch((error) => console.error(error));
        canvas.requestRenderAll();
        setCurrentPageLabel(state.activePage);
        updatePagesList();
        syncPageBackgroundControls();
        updateLayersList();
        renderEmptyState();

        if (persist) {
            scheduleLayoutSave('page-change');
        }

        if (announce) {
            emitActivity(`Switched to ${getActivePage().title || getActivePage().name || 'Page'}.`);
        }
    }

    function addPage() {
        const key = uniquePageKey(state.pages);
        const name = `Page ${state.pages.length + 1}`;
        const nextPages = [...state.pages, {
            key,
            title: name,
            page_number: state.pages.length + 1,
            background_type: 'plain_white',
            background_value: '#ffffff',
            thumbnail_path: null,
            is_locked: false,
            settings: {},
            sort_order: state.pages.length,
        }];
        state.pages = normalizePages(nextPages);
        setActivePage(key, { persist: true, announce: true });
        emitActivity(`Added ${name}.`);
    }

    function renamePage(pageKey, nextTitle = null) {
        const page = getPageByKey(pageKey);
        if (!page) {
            return;
        }

        const title = clampText(nextTitle || window.prompt('Rename page', page.title || page.name || 'Page'));
        if (!title) {
            return;
        }

        updatePageInState(pageKey, { title });
        captureHistory(`Renamed ${title}`);
        emitActivity(`Renamed page to ${title}.`);
        scheduleLayoutSave('page-rename');
    }

    function duplicatePage(pageKey) {
        const page = getPageByKey(pageKey);
        if (!page) {
            return;
        }

        const copyKey = uniquePageKey(state.pages);
        const index = Math.max(0, findInsertionIndexForPage(pageKey));
        const clonePage = {
            ...clone(page),
            key: copyKey,
            title: `${page.title || page.name || 'Page'} copy`,
            page_number: page.page_number + 1,
            thumbnail_path: null,
            sort_order: index + 1,
        };

        const nextPages = clone(state.pages);
        nextPages.splice(index + 1, 0, clonePage);
        state.pages = normalizePages(nextPages);
        setActivePage(copyKey, { persist: true, announce: true });
        captureHistory(`Duplicated ${page.title || page.name || 'page'}`);
        emitActivity(`Duplicated ${page.title || page.name || 'page'}.`);
        scheduleLayoutSave('page-duplicate');
    }

    function deletePage(pageKey) {
        if (state.pages.length <= 1) {
            window.alert('Keep at least one page on the board.');
            return;
        }

        const page = getPageByKey(pageKey);
        if (!page) {
            return;
        }

        if (!window.confirm(`Delete ${page.title || page.name || 'this page'}?`)) {
            return;
        }

        capturePageThumbnail(pageKey);
        const nextPages = state.pages.filter((item) => String(item.key) !== String(pageKey));
        const wasActive = String(state.activePage) === String(pageKey);
        state.pages = normalizePages(nextPages);

        canvas.getObjects().forEach((object) => {
            if (String(object.pageKey || pageKey) === String(pageKey)) {
                canvas.remove(object);
            }
        });

        if (wasActive) {
            state.activePage = state.pages[0]?.key || 'page-1';
        }

        setActivePage(state.activePage, { persist: true, announce: true });
        captureHistory(`Deleted ${page.title || page.name || 'page'}`);
        emitActivity(`Deleted ${page.title || page.name || 'page'}.`);
        scheduleLayoutSave('page-delete');
    }

    function setPageBackground(pageKey, backgroundType, backgroundValue = null) {
        const page = getPageByKey(pageKey);
        if (!page) {
            return;
        }

        updatePageInState(pageKey, {
            background_type: normalizeBackgroundType(backgroundType),
            background_value: backgroundValue,
        });

        if (String(pageKey) === String(state.activePage)) {
            void applyPageBackground(getActivePage()).catch((error) => console.error(error));
        }

        captureHistory(`Changed background for ${page.title || page.name || 'page'}`);
        emitActivity(`Changed background for ${page.title || page.name || 'page'}.`);
        scheduleLayoutSave('page-background');
    }

    function togglePageLock(pageKey, locked) {
        const page = getPageByKey(pageKey);
        if (!page) {
            return;
        }

        updatePageInState(pageKey, { is_locked: Boolean(locked) });
        captureHistory(`${locked ? 'Locked' : 'Unlocked'} ${page.title || page.name || 'page'}`);
        emitActivity(`${locked ? 'Locked' : 'Unlocked'} ${page.title || page.name || 'page'}.`);
        scheduleLayoutSave('page-lock');
    }

    function reorderPages(sourceKey, targetKey) {
        if (String(sourceKey) === String(targetKey)) {
            return;
        }

        const pages = clone(state.pages);
        const sourceIndex = pages.findIndex((page) => String(page.key) === String(sourceKey));
        const targetIndex = pages.findIndex((page) => String(page.key) === String(targetKey));

        if (sourceIndex < 0 || targetIndex < 0) {
            return;
        }

        const [moved] = pages.splice(sourceIndex, 1);
        pages.splice(targetIndex, 0, moved);

        state.pages = normalizePages(pages.map((page, index) => ({
            ...page,
            page_number: index + 1,
            sort_order: index,
        })));
        updatePagesList();
        captureHistory('Reordered pages');
        emitActivity('Reordered the whiteboard pages.');
        scheduleLayoutSave('page-reorder');
    }

    async function exportAllPages() {
        const activeKey = state.activePage;
        const downloads = [];

        for (const page of state.pages) {
            // eslint-disable-next-line no-await-in-loop
            setActivePage(page.key, { persist: false, announce: false });
            // eslint-disable-next-line no-await-in-loop
            await new Promise((resolve) => window.requestAnimationFrame(resolve));
            const link = document.createElement('a');
            link.download = `classbridge-whiteboard-${page.page_number || page.key}.png`;
            link.href = canvas.toDataURL({
                format: 'png',
                multiplier: 2,
                enableRetinaScaling: true,
            });
            document.body.appendChild(link);
            link.click();
            link.remove();
            downloads.push(page.key);
        }

        setActivePage(activeKey, { persist: false, announce: false });
        emitActivity(`Exported ${downloads.length} whiteboard pages.`);
    }

    function handlePageAction(action, pageKey) {
        if (!action || !pageKey) {
            return;
        }

        closePageMenus();

        if (action === 'rename-page') {
            renamePage(pageKey);
        } else if (action === 'duplicate-page') {
            duplicatePage(pageKey);
        } else if (action === 'delete-page') {
            deletePage(pageKey);
        } else if (action === 'lock-page') {
            togglePageLock(pageKey, true);
        } else if (action === 'unlock-page') {
            togglePageLock(pageKey, false);
        } else if (action === 'background-page') {
            const nextType = window.prompt(
                'Background type: plain_white, soft_grey, dark_board, grid, graph_paper, ruled_paper, dotted_paper, custom_colour, uploaded_background',
                getPageByKey(pageKey)?.background_type || 'plain_white',
            );
            if (!nextType) {
                return;
            }

            let nextValue = getPageByKey(pageKey)?.background_value || '';
            if (['custom_colour', 'soft_grey', 'dark_board'].includes(normalizeBackgroundType(nextType))) {
                nextValue = window.prompt('Background value or colour', nextValue || '#ffffff') || nextValue;
            } else if (normalizeBackgroundType(nextType) === 'uploaded_background') {
                nextValue = window.prompt('Background image URL or data URL', nextValue || '') || nextValue;
            } else if (normalizeBackgroundType(nextType) === 'pdf_page') {
                nextValue = window.prompt('PDF page URL or image placeholder', nextValue || '') || nextValue;
            } else {
                nextValue = nextValue || '#ffffff';
            }

            setPageBackground(pageKey, nextType, nextValue);
        } else if (action === 'clear-page') {
            if (window.confirm('Clear this page?')) {
                clearRemote(pageKey);
                if (typeof options.clearBoard === 'function') {
                    options.clearBoard({ pageKey }).catch((error) => console.error(error));
                }
            }
        }
    }

    function toggleRightPanel() {
        state.rightPanelOpen = !state.rightPanelOpen;
        updatePanelVisibility();
    }

    function setRightPanelTab(tab) {
        const next = ['pages', 'layers', 'templates', 'comments', 'activity'].includes(tab) ? tab : 'pages';
        state.rightPanelTab = next;
        updateTabButtons();
    }

    function applyShapeVariant(kind) {
        if (!SHAPE_VARIANTS.has(kind)) {
            return;
        }

        state.activeShape = kind;
        setTool(kind, { quiet: true });
    }

    function applyLineVariant(kind) {
        if (!LINE_VARIANTS.has(kind)) {
            return;
        }

        state.activeLineVariant = kind;
        setTool(kind, { quiet: true });
    }

    function applyTemplate(templateKey) {
        const templates = {
            'lesson-frame': {
                title: 'Lesson frame',
                subtitle: 'Headline, objective, and a quick example.',
            },
            'number-line': {
                title: 'Number line',
                subtitle: 'Start, middle, and end points for math.',
            },
            'code-card': {
                title: 'Code card',
                subtitle: 'Starter code, task, and expected output.',
            },
            'story-map': {
                title: 'Story map',
                subtitle: 'Beginning, middle, and end for writing.',
            },
        };

        const template = templates[templateKey] || templates['lesson-frame'];
        createAndAddObject('template', {
            x: canvas.width / 2,
            y: canvas.height / 2,
        }, template).catch((error) => console.error(error));
        setRightPanelTab('templates');
    }

    function insertImageFromFile(file) {
        if (!file) {
            return;
        }

        const reader = new FileReader();
        reader.onload = () => {
            createAndAddObject('image', {
                x: canvas.width / 2,
                y: canvas.height / 2,
                url: String(reader.result || ''),
                label: file.name || 'Image',
            }).then(() => setTool('select', { quiet: true }))
                .catch((error) => console.error(error));
        };
        reader.readAsDataURL(file);
    }

    function openImagePicker() {
        if (!state.imagePicker) {
            state.imagePicker = document.createElement('input');
            state.imagePicker.type = 'file';
            state.imagePicker.accept = 'image/*';
            state.imagePicker.className = 'hidden';
            document.body.appendChild(state.imagePicker);
            state.imagePicker.addEventListener('change', () => {
                const file = state.imagePicker.files?.[0] || null;
                if (file) {
                    insertImageFromFile(file);
                }
                state.imagePicker.value = '';
            });
        }

        state.imagePicker.click();
    }

    function refreshAccess() {
        const editable = typeof options.canEdit === 'function' ? Boolean(options.canEdit()) : true;
        const pointerAllowed = typeof options.canUsePointer === 'function' ? Boolean(options.canUsePointer()) : true;

        toolButtons.forEach((button) => {
            const tool = button.dataset.whiteboardTool;
            const disabled = !editable && !['select', 'hand', 'laser_pointer'].includes(tool);
            button.disabled = disabled;
            button.classList.toggle('opacity-50', disabled);
            button.classList.toggle('cursor-not-allowed', disabled);
        });

        actionButtons.forEach((button) => {
            const action = button.dataset.whiteboardAction;
            const disabled = !editable && ['undo', 'redo', 'fit-board', 'fullscreen', 'export', 'toggle-shapes-menu', 'toggle-more-menu', 'prev-page', 'next-page', 'add-page', 'duplicate-selection', 'delete-selection', 'lock-selection', 'unlock-selection', 'bring-forward', 'send-backward'].includes(action);
            button.disabled = disabled;
        });

        if (!pointerAllowed && state.activeTool === 'laser_pointer') {
            setTool('select', { quiet: true });
        }
    }

    function duplicateSelectionAction() {
        duplicateSelection().catch((error) => console.error(error));
    }

    function copySelectionAction() {
        copySelection().catch((error) => console.error(error));
    }

    function pasteSelectionAction() {
        pasteSelection().catch((error) => console.error(error));
    }

    function selectAllAction() {
        selectAllEditableObjects();
    }

    function deleteSelectionAction() {
        const active = canvas.getActiveObject();
        if (!active) {
            return;
        }

        const targets = active.type === 'activeSelection' ? active.getObjects() : [active];
        Promise.all(targets.map((object) => deleteObject(object))).catch((error) => console.error(error));
    }

    function groupSelectionAction() {
        groupSelectedObjects().catch((error) => console.error(error));
    }

    function ungroupSelectionAction() {
        ungroupSelectedObjects();
    }

    function clearPageAction() {
        if (!window.confirm('Clear the current page? This will remove every object on this page.')) {
            return;
        }

        if (typeof options.clearBoard === 'function') {
            options.clearBoard({ pageKey: state.activePage }).catch((error) => console.error(error));
        }
    }

    function resetZoomAction() {
        resetZoom();
    }

    function alignSelectionAction(align) {
        alignSelectedObjects(align);
    }

    function distributeSelectionAction(direction) {
        distributeSelectedObjects(direction);
    }

    function toggleLockSelection(locked) {
        const object = getSelectedObject();
        if (!object) {
            return;
        }

        object.set({
            selectable: !locked,
            evented: !locked,
            cbLocked: locked,
        });
        canvas.requestRenderAll();
        syncObject(object).catch((error) => console.error(error));
        emitActivity(`${locked ? 'Locked' : 'Unlocked'} ${createDefaultObjectLabel(object)}.`);
    }

    function bringForward() {
        const object = getSelectedObject();
        if (!object) {
            return;
        }

        canvas.bringForward(object);
        canvas.requestRenderAll();
        syncObject(object).catch((error) => console.error(error));
        emitActivity(`Moved ${createDefaultObjectLabel(object)} forward.`);
    }

    function sendBackward() {
        const object = getSelectedObject();
        if (!object) {
            return;
        }

        canvas.sendBackwards(object);
        canvas.requestRenderAll();
        syncObject(object).catch((error) => console.error(error));
        emitActivity(`Moved ${createDefaultObjectLabel(object)} backward.`);
    }

    function bringToFront() {
        const object = getSelectedObject();
        if (!object) {
            return;
        }

        canvas.bringToFront(object);
        canvas.requestRenderAll();
        syncObject(object).catch((error) => console.error(error));
        emitActivity(`Moved ${createDefaultObjectLabel(object)} to the front.`);
    }

    function sendToBack() {
        const object = getSelectedObject();
        if (!object) {
            return;
        }

        canvas.sendToBack(object);
        canvas.requestRenderAll();
        syncObject(object).catch((error) => console.error(error));
        emitActivity(`Moved ${createDefaultObjectLabel(object)} to the back.`);
    }

    async function undo() {
        if (state.historyIndex <= 0) {
            return;
        }

        state.historyIndex -= 1;
        await restoreHistorySnapshot(state.history[state.historyIndex]);
        emitActivity('Undid the last board change.');
    }

    async function redo() {
        if (state.historyIndex >= state.history.length - 1) {
            return;
        }

        state.historyIndex += 1;
        await restoreHistorySnapshot(state.history[state.historyIndex]);
        emitActivity('Redid the board change.');
    }

    async function restoreHistorySnapshot(snapshot) {
        if (!snapshot) {
            return;
        }

        state.isRestoring = true;
        await new Promise((resolve) => {
            canvas.loadFromJSON(snapshot.canvas, () => {
                canvas.getObjects().forEach((object) => {
                    object.set({
                        cornerStyle: 'circle',
                        cornerColor: '#0f172a',
                        cornerStrokeColor: '#ffffff',
                        transparentCorners: false,
                    });
                    object.visible = String(object.pageKey || state.activePage) === (snapshot.whiteboardState?.active_page || state.activePage);
                });
                resolve();
            });
        });
        state.isRestoring = false;
        applyState(snapshot.whiteboardState || getState());
        canvas.requestRenderAll();
        renderEmptyState();
        updateLayersList();
        await syncAllObjectsToServer();
    }

    async function syncAllObjectsToServer() {
        const objects = canvas.getObjects();
        const presentIds = new Set();

        for (const object of objects) {
            // eslint-disable-next-line no-await-in-loop
            const response = await syncObject(object, object.whiteboardElementId ? 'upsert' : 'upsert');
            if (response?.element?.id) {
                presentIds.add(String(response.element.id));
            } else if (object.whiteboardElementId) {
                presentIds.add(String(object.whiteboardElementId));
            }
        }

        for (const [id] of state.serverElements.entries()) {
            if (!presentIds.has(String(id))) {
                // eslint-disable-next-line no-await-in-loop
                await options.deleteElement?.(id);
                state.serverElements.delete(String(id));
            }
        }
    }

    function moveZoom(delta) {
        const next = clamp((state.zoom || 100) + delta, 20, 300, 100) / 100;
        canvas.zoomToPoint(new fabric.Point(canvas.width / 2, canvas.height / 2), next);
        state.viewport = {
            x: canvas.viewportTransform?.[4] || 0,
            y: canvas.viewportTransform?.[5] || 0,
        };
        setZoomLabel(next);
        scheduleLayoutSave('zoom-change');
    }

    function fitToScreen() {
        const bounds = getBoundingBox(canvas.getObjects().filter((object) => object.visible !== false && object.pageKey === state.activePage));
        if (!bounds) {
            canvas.setViewportTransform([1, 0, 0, 1, 0, 0]);
            canvas.setZoom(1);
            setZoomLabel(1);
            scheduleLayoutSave('fit');
            return;
        }

        const padding = 80;
        const scale = Math.min(
            (canvas.getWidth() - padding * 2) / bounds.width,
            (canvas.getHeight() - padding * 2) / bounds.height,
            1.5,
        );

        const zoom = clamp(scale, 0.25, 2.5, 1);
        canvas.setZoom(zoom);
        const center = new fabric.Point(bounds.left + bounds.width / 2, bounds.top + bounds.height / 2);
        canvas.absolutePan(new fabric.Point(
            center.x * zoom - canvas.getWidth() / 2,
            center.y * zoom - canvas.getHeight() / 2,
        ));

        state.viewport = {
            x: canvas.viewportTransform?.[4] || 0,
            y: canvas.viewportTransform?.[5] || 0,
        };
        setZoomLabel(zoom);
        scheduleLayoutSave('fit');
    }

    function toggleFullscreen() {
        const element = root;
        if (document.fullscreenElement) {
            document.exitFullscreen().catch(() => null);
            return;
        }

        if (element.requestFullscreen) {
            element.requestFullscreen().catch(() => null);
        }
    }

    function exportImage() {
        const link = document.createElement('a');
        link.download = `classbridge-whiteboard-${state.activePage}.png`;
        link.href = canvas.toDataURL({
            format: 'png',
            multiplier: 2,
            enableRetinaScaling: true,
        });
        document.body.appendChild(link);
        link.click();
        link.remove();
        emitActivity('Exported the board as an image.');
    }

    function updatePropertySelectionFromActiveObject() {
        const object = getSelectedObject();
        updatePropertiesVisibility(object);
        if (!object) {
            return;
        }
    }

    async function handleCanvasPointerDown(opt) {
        const point = getCanvasPoint(opt);
        const editable = typeof options.canEdit === 'function' ? Boolean(options.canEdit()) : true;
        const shouldPan = state.activeTool === 'hand' || state.spacePressed;

        if (shouldPan) {
            state.isPanning = true;
            state.panStart = { x: point.x, y: point.y, viewport: clone(canvas.viewportTransform) };
            canvas.selection = false;
            canvas.hoverCursor = 'grabbing';
            return;
        }

        if (state.activeTool === 'eraser') {
            state.isErasing = true;
            eraseObjectAtPoint(opt);
            return;
        }

        if (state.activeTool === 'laser_pointer' || (editable && state.activeTool !== 'select' && !INSERT_VARIANTS.has(state.activeTool))) {
            if (typeof options.sendPointer === 'function') {
                options.sendPointer(point, 'whiteboard');
            }
        }

        if (!editable) {
            return;
        }

        if (state.activeTool === 'image') {
            openImagePicker();
            return;
        }

        if (!INSERT_VARIANTS.has(state.activeTool)) {
            return;
        }

        if (state.activeTool === 'text') {
            const textObject = await createAndAddObject('text', point, { text: 'Type here' });
            if (textObject) {
                canvas.setActiveObject(textObject);
                canvas.requestRenderAll();
                textObject.enterEditing();
                textObject.selectAll();
                updatePropertySelectionFromActiveObject();
            }
            return;
        }

        const object = await createAndAddObject(
            state.activeTool === 'templates' ? 'template' : state.activeTool,
            point,
            state.activeTool === 'templates'
                ? { title: 'Lesson frame', subtitle: 'Objective and example' }
                : { stroke: state.color, fill: state.fill, strokeWidth: state.strokeWidth, opacity: state.opacity },
        );

        if (object) {
            if (state.activeTool === 'templates') {
                setRightPanelTab('templates');
            }

            updatePropertySelectionFromActiveObject();
        }
    }

    function handleCanvasPointerMove(opt) {
        const point = getCanvasPoint(opt);

        if (typeof options.sendPointer === 'function') {
            const now = Date.now();
            if (now - state.lastPointerSent > 350) {
                state.lastPointerSent = now;
                options.sendPointer(point, 'whiteboard');
            }
        }

        if (state.activeTool === 'eraser' && state.isErasing) {
            eraseObjectAtPoint(opt);
            return;
        }

        if (state.activeTool === 'pen' || state.activeTool === 'highlighter') {
            const pressure = Number(opt.e?.pressure || opt.e?.force || 0);
            if (pressure > 0) {
                state.pointerPressure = pressure;
                updateBrushSettings(pressure);
            }
        }

        if ((state.activeTool === 'hand' || state.spacePressed) && state.isPanning && state.panStart?.viewport) {
            const deltaX = point.x - state.panStart.x;
            const deltaY = point.y - state.panStart.y;
            const viewport = clone(state.panStart.viewport) || [1, 0, 0, 1, 0, 0];
            viewport[4] += deltaX;
            viewport[5] += deltaY;
            canvas.setViewportTransform(viewport);
            canvas.requestRenderAll();
        }
    }

    function handleCanvasPointerUp() {
        if (state.isPanning) {
            state.isPanning = false;
            canvas.hoverCursor = state.activeTool === 'hand' ? 'grab' : 'default';
            state.viewport = {
                x: canvas.viewportTransform?.[4] || 0,
                y: canvas.viewportTransform?.[5] || 0,
            };
            scheduleLayoutSave('pan');
        }

        if (state.isErasing) {
            state.isErasing = false;
            state.erasedKeys = new Set();
        }

        state.pointerPressure = 1;
    }

    function handleMouseWheel(opt) {
        opt.e.preventDefault();
        const delta = opt.e.deltaY;
        const currentZoom = canvas.getZoom();
        const factor = delta > 0 ? 0.95 : 1.05;
        const next = clamp(currentZoom * factor, 0.2, 3, 1);
        canvas.zoomToPoint(new fabric.Point(opt.e.offsetX, opt.e.offsetY), next);
        state.viewport = {
            x: canvas.viewportTransform?.[4] || 0,
            y: canvas.viewportTransform?.[5] || 0,
        };
        setZoomLabel(next);
        scheduleLayoutSave('zoom-change');
    }

    function getTouchDistance(touchA, touchB) {
        return Math.hypot(touchA.clientX - touchB.clientX, touchA.clientY - touchB.clientY);
    }

    function getTouchMidpoint(touchA, touchB) {
        return {
            x: (touchA.clientX + touchB.clientX) / 2,
            y: (touchA.clientY + touchB.clientY) / 2,
        };
    }

    function bindTouchGestures() {
        const target = canvas.upperCanvasEl;
        if (!target) {
            return;
        }

        target.style.touchAction = 'none';

        target.addEventListener('touchstart', (event) => {
            if (event.touches.length !== 2) {
                return;
            }

            const [touchA, touchB] = Array.from(event.touches);
            state.pinching = true;
            state.pinchTouches = [touchA.identifier, touchB.identifier];
            state.pinchStartDistance = Math.max(getTouchDistance(touchA, touchB), 1);
            state.pinchStartZoom = canvas.getZoom();
            state.pinchStartCenter = getTouchMidpoint(touchA, touchB);
            event.preventDefault();
        }, { passive: false });

        target.addEventListener('touchmove', (event) => {
            if (!state.pinching || event.touches.length < 2) {
                return;
            }

            const touchA = [...event.touches].find((touch) => touch.identifier === state.pinchTouches[0]) || event.touches[0];
            const touchB = [...event.touches].find((touch) => touch.identifier === state.pinchTouches[1]) || event.touches[1];
            const currentDistance = Math.max(getTouchDistance(touchA, touchB), 1);
            const midpoint = getTouchMidpoint(touchA, touchB);
            const nextZoom = clamp(state.pinchStartZoom * (currentDistance / state.pinchStartDistance), 0.2, 3, 1);

            canvas.zoomToPoint(
                new fabric.Point(
                    midpoint.x - (canvas.upperCanvasEl?.getBoundingClientRect?.().left || canvasContainer.getBoundingClientRect().left),
                    midpoint.y - (canvas.upperCanvasEl?.getBoundingClientRect?.().top || canvasContainer.getBoundingClientRect().top),
                ),
                nextZoom,
            );
            setZoomLabel(nextZoom);
            state.viewport = {
                x: canvas.viewportTransform?.[4] || 0,
                y: canvas.viewportTransform?.[5] || 0,
            };
            canvas.requestRenderAll();
            scheduleLayoutSave('pinch');
            event.preventDefault();
        }, { passive: false });

        const endGesture = () => {
            if (!state.pinching) {
                return;
            }

            state.pinching = false;
            state.pinchTouches = [];
        };

        target.addEventListener('touchend', endGesture);
        target.addEventListener('touchcancel', endGesture);
    }

    function handleObjectModified(opt) {
        const object = opt.target;
        if (!object || state.isRestoring || state.suppressHistory) {
            return;
        }

        setObjectMeta(object, {
            pageKey: state.activePage,
        });
        canvas.requestRenderAll();
        updateLayersList();
        updatePropertiesVisibility(object);
        captureHistory(`Modified ${createDefaultObjectLabel(object)}`);
        syncObject(object).catch((error) => console.error(error));
    }

    function handleObjectRemoved(opt) {
        const object = opt.target;
        if (!object || state.isRestoring || state.suppressHistory) {
            return;
        }

        updateLayersList();
        renderEmptyState();
        updatePropertiesVisibility(null);
        captureHistory(`Removed ${createDefaultObjectLabel(object)}`);
    }

    function handlePathCreated(opt) {
        const path = opt.path;
        if (!path || state.isRestoring) {
            return;
        }

        setObjectMeta(path, {
            pageKey: state.activePage,
            kind: state.activeTool,
            tool: state.activeTool,
            layerName: state.activeTool === 'eraser' ? 'Eraser stroke' : 'Freehand stroke',
        });
        path.visible = true;
        canvas.requestRenderAll();
        updateLayersList();
        captureHistory(`Added ${createDefaultObjectLabel(path)}`);
        syncObject(path).catch((error) => console.error(error));
    }

    function handleSelectionChanged() {
        const object = getSelectedObject();
        updatePropertiesVisibility(object);
        updateLayersList();
    }

    function bindUi() {
        updateToolButtons();
        updateTabButtons();
        updatePagesList();
        updateLayersList();
        updateCommentsList();
        updateActivityList();
        updateSnapshotsList();
        updatePanelVisibility();
        updatePropertiesVisibility(null);

        toolButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const tool = button.dataset.whiteboardTool || 'select';
                if (tool === 'shapes' || tool === 'more_tools') {
                    return;
                }
                if (tool === 'templates') {
                    setRightPanelTab('templates');
                    setTool('select', { quiet: true });
                    closeMenus();
                    return;
                }

                setTool(tool);
                closeMenus();
            });
        });

        actionButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const action = button.dataset.whiteboardAction;

                if (action === 'undo') {
                    undo().catch((error) => console.error(error));
                } else if (action === 'redo') {
                    redo().catch((error) => console.error(error));
                } else if (action === 'fit-board') {
                    fitToScreen();
                } else if (action === 'fullscreen') {
                    toggleFullscreen();
                } else if (action === 'export') {
                    exportImage();
                } else if (action === 'toggle-right-panel') {
                    toggleRightPanel();
                } else if (action === 'toggle-shapes-menu') {
                    toggleMenu(shapesMenu);
                } else if (action === 'toggle-lines-menu') {
                    toggleMenu(linesMenu);
                } else if (action === 'toggle-more-menu') {
                    toggleMenu(moreMenu);
                } else if (action === 'clear-board') {
                    clearPageAction();
                } else if (action === 'clear-page') {
                    clearPageAction();
                } else if (action === 'duplicate-selection') {
                    duplicateSelectionAction();
                } else if (action === 'copy-selection') {
                    copySelectionAction();
                } else if (action === 'paste-selection') {
                    pasteSelectionAction();
                } else if (action === 'select-all') {
                    selectAllAction();
                } else if (action === 'group-selection') {
                    groupSelectionAction();
                } else if (action === 'ungroup-selection') {
                    ungroupSelectionAction();
                } else if (action === 'align-left') {
                    alignSelectionAction('left');
                } else if (action === 'align-right') {
                    alignSelectionAction('right');
                } else if (action === 'align-top') {
                    alignSelectionAction('top');
                } else if (action === 'align-bottom') {
                    alignSelectionAction('bottom');
                } else if (action === 'align-center') {
                    alignSelectionAction('center');
                } else if (action === 'align-middle') {
                    alignSelectionAction('middle');
                } else if (action === 'distribute-horizontal') {
                    distributeSelectionAction('horizontal');
                } else if (action === 'distribute-vertical') {
                    distributeSelectionAction('vertical');
                } else if (action === 'delete-selection') {
                    deleteSelectionAction();
                } else if (action === 'lock-selection') {
                    toggleLockSelection(true);
                } else if (action === 'unlock-selection') {
                    toggleLockSelection(false);
                } else if (action === 'bring-forward') {
                    bringForward();
                } else if (action === 'send-backward') {
                    sendBackward();
                } else if (action === 'bring-to-front') {
                    bringToFront();
                } else if (action === 'send-to-back') {
                    sendToBack();
                } else if (action === 'zoom-in') {
                    moveZoom(10);
                } else if (action === 'zoom-out') {
                    moveZoom(-10);
                } else if (action === 'reset-zoom') {
                    resetZoomAction();
                } else if (action === 'prev-page') {
                    const index = state.pages.findIndex((page) => page.key === state.activePage);
                    if (index > 0) {
                        setActivePage(state.pages[index - 1].key, { persist: true, announce: true });
                    }
                } else if (action === 'next-page') {
                    const index = state.pages.findIndex((page) => page.key === state.activePage);
                    if (index < state.pages.length - 1) {
                        setActivePage(state.pages[index + 1].key, { persist: true, announce: true });
                    }
                } else if (action === 'add-page') {
                    addPage();
                } else if (action === 'create-snapshot') {
                    capturePageThumbnail(state.activePage);
                    if (typeof options.createSnapshot === 'function') {
                        options.createSnapshot({
                            name: `${getActivePage().title || getActivePage().name || 'Page'} snapshot`,
                            reason: 'manual',
                            pageKey: state.activePage,
                        }).catch((error) => console.error(error));
                    }
                } else if (action === 'export-all-pages') {
                    exportAllPages().catch((error) => console.error(error));
                } else if (action === 'apply-page-background') {
                    const nextType = pageBackgroundTypeSelect?.value || 'plain_white';
                    const nextValue = pageBackgroundValueInput?.value || null;
                    setPageBackground(state.activePage, nextType, nextValue);
                }
            });
        });

        insertShapeButtons.forEach((button) => {
            button.addEventListener('click', () => {
                applyShapeVariant(button.dataset.whiteboardInsertShape || 'rectangle');
                closeMenus();
            });
        });

        insertLineButtons.forEach((button) => {
            button.addEventListener('click', () => {
                applyLineVariant(button.dataset.whiteboardInsertLine || 'line');
                closeMenus();
            });
        });

        templateButtons.forEach((button) => {
            button.addEventListener('click', () => {
                applyTemplate(button.dataset.whiteboardTemplate || 'lesson-frame');
            });
        });

        tabButtons.forEach((button) => {
            button.addEventListener('click', () => {
                setRightPanelTab(button.dataset.whiteboardTab || 'pages');
            });
        });

        if (pagesList) {
            pagesList.addEventListener('click', (event) => {
                const button = event.target.closest('[data-whiteboard-page-action]');
                if (!button) {
                    return;
                }

                const pageKey = button.dataset.whiteboardPageKey || button.closest('[data-whiteboard-page-card]')?.dataset.whiteboardPage;
                if (pageKey) {
                    handlePageAction(button.dataset.whiteboardPageAction || '', pageKey);
                }
            });

            pagesList.addEventListener('dragend', () => {
                state.draggingPageKey = null;
            });
        }

        colorInputs.forEach((input) => {
            input.addEventListener('input', () => setColor(input.value));
        });

        fillInputs.forEach((input) => {
            input.addEventListener('input', () => setFill(input.value));
        });

        widthInputs.forEach((input) => {
            input.addEventListener('input', () => setStrokeWidth(Number(input.value)));
        });

        opacityInputs.forEach((input) => {
            input.addEventListener('input', () => setOpacity(Number(input.value)));
        });

        fontSizeInputs.forEach((input) => {
            input.addEventListener('input', () => setFontSize(Number(input.value)));
        });

        propertyFields.forEach((input) => {
            const field = input.dataset.whiteboardPropField || '';
            if (field === 'tableCells') {
                return;
            }

            const eventName = input.tagName === 'TEXTAREA' || ['text', 'color', 'range'].includes(String(input.type || '').toLowerCase())
                ? 'input'
                : 'change';

            input.addEventListener(eventName, () => applyInspectorChange(field, input.value));
        });

        textStyleButtons.forEach((button) => {
            button.addEventListener('click', () => applyTextStyle(button.dataset.whiteboardTextStyle || 'bold'));
        });

        textAlignButtons.forEach((button) => {
            button.addEventListener('click', () => applyTextAlign(button.dataset.whiteboardTextAlign || 'left'));
        });

        equationInsertButtons.forEach((button) => {
            button.addEventListener('click', () => insertEquationSymbol(button.dataset.whiteboardEquationInsert || ''));
        });

        if (tableApplyButton) {
            tableApplyButton.addEventListener('click', applyTableInspector);
        }

        if (objectLockButton) {
            objectLockButton.addEventListener('click', () => {
                const object = getSelectedObject();
                if (!object) {
                    return;
                }

                toggleLockSelection(!object.cbLocked);
            });
        }

        document.addEventListener('click', (event) => {
            if (!root.contains(event.target)) {
                closeMenus();
            }
        });
    }

    function toggleMenu(menu) {
        [shapesMenu, linesMenu, moreMenu].forEach((item) => {
            if (item !== menu) {
                item?.classList.add('hidden');
            }
        });

        if (!menu) {
            return;
        }

        menu.classList.toggle('hidden');
    }

    function closeMenus() {
        shapesMenu?.classList.add('hidden');
        linesMenu?.classList.add('hidden');
        moreMenu?.classList.add('hidden');
        closePageMenus();
    }

    function bindCanvasEvents() {
        canvas.on('mouse:down', handleCanvasPointerDown);
        canvas.on('mouse:move', handleCanvasPointerMove);
        canvas.on('mouse:up', handleCanvasPointerUp);
        canvas.on('mouse:wheel', handleMouseWheel);
        canvas.on('object:modified', handleObjectModified);
        canvas.on('object:removed', handleObjectRemoved);
        canvas.on('path:created', handlePathCreated);
        canvas.on('selection:created', handleSelectionChanged);
        canvas.on('selection:updated', handleSelectionChanged);
        canvas.on('selection:cleared', handleSelectionChanged);
    }

    function bindKeyboardShortcuts() {
        root.setAttribute('tabindex', '0');
        const handleShortcut = (event) => {
            const active = canvas.getActiveObject();
            const editingText = isCanvasTextEditing();
            const targetTag = String(event.target?.tagName || '').toLowerCase();
            const targetEditable = Boolean(event.target?.isContentEditable);
            const typingField = ['input', 'textarea', 'select'].includes(targetTag) || targetEditable;

            if (typingField && !editingText) {
                return;
            }

            if (event.code === 'Space' && !editingText) {
                event.preventDefault();
                state.spacePressed = event.type === 'keydown';
                if (state.spacePressed) {
                    canvas.defaultCursor = 'grab';
                    canvas.hoverCursor = 'grab';
                } else if (state.activeTool === 'hand') {
                    canvas.defaultCursor = 'grab';
                    canvas.hoverCursor = 'grab';
                } else {
                    canvas.defaultCursor = state.activeTool === 'select' ? 'default' : 'crosshair';
                    canvas.hoverCursor = state.activeTool === 'select' ? 'move' : 'crosshair';
                }
                return;
            }

            if (event.type !== 'keydown') {
                return;
            }

            if (event.key === 'Escape') {
                closeMenus();
                if (editingText && active?.exitEditing) {
                    active.exitEditing();
                    canvas.discardActiveObject();
                    canvas.requestRenderAll();
                } else if (canvas.getActiveObject()) {
                    canvas.discardActiveObject();
                    canvas.requestRenderAll();
                }
                return;
            }

            if (editingText) {
                return;
            }

            if ((event.ctrlKey || event.metaKey) && event.shiftKey && event.key.toLowerCase() === 'z') {
                event.preventDefault();
                redo().catch((error) => console.error(error));
                return;
            }

            if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'y') {
                event.preventDefault();
                redo().catch((error) => console.error(error));
                return;
            }

            if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'z') {
                event.preventDefault();
                undo().catch((error) => console.error(error));
                return;
            }

            if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'c') {
                event.preventDefault();
                copySelectionAction();
                return;
            }

            if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'v') {
                event.preventDefault();
                pasteSelectionAction();
                return;
            }

            if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'd') {
                event.preventDefault();
                duplicateSelectionAction();
                return;
            }

            if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'a') {
                event.preventDefault();
                selectAllAction();
                return;
            }

            if ((event.key === 'Delete' || event.key === 'Backspace') && getSelectedObject()) {
                event.preventDefault();
                deleteSelectionAction();
            }
        };

        window.addEventListener('keydown', handleShortcut, { passive: false });
        window.addEventListener('keyup', handleShortcut, { passive: false });
    }

    function refreshAccess() {
        const editable = typeof options.canEdit === 'function' ? Boolean(options.canEdit()) : true;
        const pointerAllowed = typeof options.canUsePointer === 'function' ? Boolean(options.canUsePointer()) : true;

        toolButtons.forEach((button) => {
            const tool = button.dataset.whiteboardTool || 'select';
            const isMenu = tool === 'shapes' || tool === 'more_tools';
            const disabled = !editable && !['select', 'hand', 'laser_pointer'].includes(tool);
            button.disabled = disabled;
            button.classList.toggle('opacity-50', disabled);
            button.classList.toggle('cursor-not-allowed', disabled);
            if (isMenu) {
                button.disabled = !editable;
            }
        });

        [...insertShapeButtons, ...insertLineButtons, ...templateButtons].forEach((button) => {
            button.disabled = !editable;
        });

        actionButtons.forEach((button) => {
            const action = button.dataset.whiteboardAction;
            const editActions = [
                'duplicate-selection',
                'copy-selection',
                'paste-selection',
                'select-all',
                'delete-selection',
                'group-selection',
                'ungroup-selection',
                'align-left',
                'align-right',
                'align-top',
                'align-bottom',
                'align-center',
                'align-middle',
                'distribute-horizontal',
                'distribute-vertical',
                'lock-selection',
                'unlock-selection',
                'bring-forward',
                'send-backward',
                'bring-to-front',
                'send-to-back',
                'add-page',
                'clear-page',
                'reset-zoom',
            ];
            const disabled = !editable && editActions.includes(action);
            button.disabled = disabled;
        });

        propertyFields.forEach((field) => {
            field.disabled = !editable;
            field.classList.toggle('opacity-60', !editable);
            field.classList.toggle('cursor-not-allowed', !editable);
        });

        [...colorInputs, ...fillInputs, ...widthInputs, ...opacityInputs, ...fontSizeInputs].forEach((field) => {
            field.disabled = !editable;
            field.classList.toggle('opacity-60', !editable);
            field.classList.toggle('cursor-not-allowed', !editable);
        });

        textStyleButtons.forEach((button) => {
            button.disabled = !editable;
        });

        textAlignButtons.forEach((button) => {
            button.disabled = !editable;
        });

        if (objectLockButton) {
            objectLockButton.disabled = !editable;
        }

        if (tableApplyButton) {
            tableApplyButton.disabled = !editable;
        }

        equationInsertButtons.forEach((button) => {
            button.disabled = !editable;
        });

        if (!pointerAllowed && state.activeTool === 'laser_pointer') {
            setTool('select', { quiet: true });
        }
    }

    function applyToolFromSelection() {
        const object = getSelectedObject();
        updatePropertiesVisibility(object);
    }

    async function loadFromRecords(elements = []) {
        await loadElements(elements);
        renderEmptyState();
    }

    function setPages(pages, activePage = null, { persist = false } = {}) {
        state.pages = normalizePages(pages);
        if (activePage) {
            state.activePage = activePage;
        } else if (!state.pages.find((page) => page.key === state.activePage)) {
            state.activePage = state.pages[0]?.key || 'page-1';
        }

        updatePagesList();
        setCurrentPageLabel(state.activePage);
        void applyPageBackground(getActivePage()).catch((error) => console.error(error));
        syncPageBackgroundControls();
        renderEmptyState();

        if (persist) {
            schedulePageThumbnailCapture(state.activePage);
            scheduleLayoutSave('pages');
        }
    }

    function setWhiteboardState(whiteboardState = {}, { persist = false } = {}) {
        const next = {
            ...DEFAULT_WHITEBOARD_STATE,
            ...clone(whiteboardState),
        };

        setPages(next.pages || DEFAULT_WHITEBOARD_STATE.pages, next.active_page || 'page-1', { persist });
        state.settings = {
            ...clone(DEFAULT_WHITEBOARD_STATE.settings || {}),
            ...(next.settings || {}),
        };
        setZoomLabel(clamp(next.zoom || 100, 20, 300, 100) / 100);
        state.viewport = {
            x: Number(next.viewport?.x || 0),
            y: Number(next.viewport?.y || 0),
        };
        canvas.setViewportTransform([1, 0, 0, 1, state.viewport.x, state.viewport.y]);
        canvas.setZoom(clamp(next.zoom || 100, 20, 300, 100) / 100);
        void applyPageBackground(getActivePage()).catch((error) => console.error(error));
        syncPageBackgroundControls();
        canvas.requestRenderAll();
    }

    function resize() {
        setCanvasSize();
        canvas.requestRenderAll();
    }

    function destroy() {
        canvas.dispose();
        state.imagePicker?.remove?.();
    }

    bindUi();
    bindCanvasEvents();
    bindTouchGestures();
    bindKeyboardShortcuts();
    setPages(state.pages, state.activePage, { persist: false });
    setWhiteboardState(options.whiteboardState || DEFAULT_WHITEBOARD_STATE, { persist: false });
    setTool('select', { quiet: true });
    setConnectionState(options.connectionState || 'Online');
    setAutosaveState('Saved');
    setCanvasSize();
    updatePanelVisibility();
    renderEmptyState();

    return {
        canvas,
        setTool,
        setColor,
        setFill,
        setStrokeWidth,
        setOpacity,
        setFontSize,
        refreshAccess,
        setConnectionState,
        setAutosaveState,
        setPages,
        setWhiteboardState,
        setRightPanelTab,
        setRightPanelOpen(value) {
            state.rightPanelOpen = Boolean(value);
            updatePanelVisibility();
        },
        setSnapshots(snapshots = []) {
            state.snapshots = Array.isArray(snapshots) ? snapshots : [];
            updateSnapshotsList();
        },
        getState,
        loadElements: loadFromRecords,
        applyRemoteElement,
        removeRemoteElement,
        clearRemote,
        addPage,
        setActivePage,
        renamePage,
        duplicatePage,
        deletePage,
        reorderPages,
        setPageBackground,
        togglePageLock,
        exportAllPages,
        undo,
        redo,
        fitToScreen,
        toggleFullscreen,
        exportImage,
        resize,
        duplicateSelection: duplicateSelectionAction,
        copySelection: copySelectionAction,
        pasteSelection: pasteSelectionAction,
        selectAll: selectAllAction,
        deleteSelection: deleteSelectionAction,
        groupSelection: groupSelectionAction,
        ungroupSelection: ungroupSelectionAction,
        bringForward,
        sendBackward,
        bringToFront,
        sendToBack,
        resetZoom: resetZoomAction,
        syncAllObjectsToServer,
        persistLayout: scheduleLayoutSave,
        addActivity: emitActivity,
        getSelectedObject,
        applyToolFromSelection,
    };
}
