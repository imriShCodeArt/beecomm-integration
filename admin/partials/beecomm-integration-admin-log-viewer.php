<div class="wrap">
    <h1>צפייה בלוג</h1>
    <table class="widefat striped log-table">
        <thead>
            <tr>
                <th style="width:20%;">Time</th>
                <th style="width:10%;">Level</th>
                <th>Message</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($log_entries as $entry): ?>
                <tr class="log-row level-<?php echo esc_attr($entry['level']); ?>">
                    <td class="time_columns" style="width:20%;"><?php echo esc_html($entry['time']); ?></td>
                    <td><?php echo esc_html(strtoupper($entry['level'])); ?></td>
                    <td class="message_columns"">
                        <pre><?php echo $entry['message']; ?></pre>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    function decodeUnicodeString(input) {
        try {
            return JSON.parse('"' + input.replace(/"/g, ' \\"') + '"');
        } catch (e) { return input; }
    } function
        decodeJsonContent(raw) {
            try {
                let jsonPart = raw.includes('::') ? raw.split('::')[1] : raw; let
                    parsed = JSON.parse(jsonPart); return JSON.stringify(parsed, null, 2); // pretty print } catch (e) {
                return raw;
            } } function decodeTimeColumn(td) {
                if (!td || td.dataset.decoded) return; const
                    raw = td.textContent.trim(); const jsonPart = raw.includes('::') ? raw.split('::')[1] : raw; const
                        decoded = decodeUnicodeString(jsonPart); td.textContent = decoded; td.dataset.decoded = 'true';
            }
    function decodeMessageColumn(td) {
        if (!td || td.dataset.decoded) return; const
            pre = td.querySelector('pre'); if (!pre) return; const raw = pre.textContent.trim(); let
                jsonPart = raw.includes('::') ? raw.split('::')[1] : raw; try {
                    const data = JSON.parse(jsonPart); const
                        html = renderJsonAsHtml(data); pre.outerHTML = `<div class="decoded-json">${html}
</div>`;
                    td.dataset.decoded = 'true';
                } catch (e) {
                    // Fallback to prettified JSON
                    try {
                        const pretty = JSON.stringify(JSON.parse(jsonPart), null, 2);
                        pre.textContent = pretty;
                    } catch (err) {
                        pre.textContent = raw;
                    }
                }
    }

    function renderJsonAsHtml(obj, indent = 0) {
        if (typeof obj !== 'object' || obj === null) return `<code>${String(obj)}</code>`;

        const isArray = Array.isArray(obj);
        const entries = Object.entries(obj);

        const padding = '&nbsp;'.repeat(indent * 4);
        const open = isArray ? '[' : '{';
        const close = isArray ? ']' : '}';

        let result = `<div style="margin-bottom: 5px;">${padding}${open}</div>`;
        for (const [key, value] of entries) {
            result += `<div style="margin-left: ${indent * 20}px;">
    ${padding}<strong>${isArray ? '' : key + ': '}</strong>${typeof value === 'object' && value !== null
                    ? renderJsonAsHtml(value, indent + 1)
                    : `<span>${value}</span>`}
</div>`;
        }
        result += `<div style="margin-bottom: 5px;">${padding}${close}</div>`;
        return result;
    }


    // Decode existing rows on DOMContentLoaded
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('td.time_columns').forEach(decodeTimeColumn);
        document.querySelectorAll('td.message_columns').forEach(decodeMessageColumn);
    });

    // Watch for added rows or cells
    const observer = new MutationObserver((mutationsList) => {
        for (const mutation of mutationsList) {
            mutation.addedNodes.forEach(node => {
                if (node.nodeType === 1) {
                    if (node.matches?.('td.time_columns')) decodeTimeColumn(node);
                    if (node.matches?.('td.message_columns')) decodeMessageColumn(node);

                    node.querySelectorAll?.('td.time_columns').forEach(decodeTimeColumn);
                    node.querySelectorAll?.('td.message_columns').forEach(decodeMessageColumn);
                }
            });
        }
    });

    // Start observing table body
    const tableBody = document.querySelector('.log-table tbody');
    if (tableBody) {
        observer.observe(tableBody, { childList: true, subtree: true });
    }
</script>



<style>
    .log-table pre {
        margin: 0;
        white-space: pre-wrap;
        word-break: break-word;
    }

    .level-error td {
        background: #ffe5e5;
    }

    .level-warning td {
        background: #fff8e5;
    }

    .level-info td {
        background: #e5f5ff;
    }

    .level-debug td {
        background: #f1f1f1;
    }

    .time_columns {
        max-width: 100px !important;
    }
</style>