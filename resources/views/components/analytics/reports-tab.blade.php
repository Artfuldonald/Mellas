<div x-data="{
    selectedReport: 'Financial Summary',
    reportTypes: [
        'Financial Summary',
        'Customer Acquisition',
        'Product Performance',
        'Risk Assessment',
        'Marketing Campaign Analysis',
        'Operational Efficiency'
    ],
    reportData: {
        'Financial Summary': [
            { id: 1, metric: 'Total Revenue', value: '$1,234,567' },
            { id: 2, metric: 'Net Profit', value: '$345,678' },
            { id: 3, metric: 'Operating Expenses', value: '$567,890' },
            { id: 4, metric: 'Gross Margin', value: '28%' },
            { id: 5, metric: 'Return on Investment', value: '15%' }
        ],
        'Customer Acquisition': [
            { id: 1, metric: 'New Customers', value: '1,234' },
            { id: 2, metric: 'Customer Acquisition Cost', value: '$50' },
            { id: 3, metric: 'Conversion Rate', value: '3.5%' },
            { id: 4, metric: 'Customer Lifetime Value', value: '$1,200' },
            { id: 5, metric: 'Churn Rate', value: '2.3%' }
        ]
    },
    
    handleGenerateReport() {
        console.log(`Generating ${this.selectedReport} report...`);
    },
    
    handleDownloadReport() {
        console.log(`Downloading ${this.selectedReport} report...`);
    },
    
    handlePrintReport() {
        console.log(`Printing ${this.selectedReport} report...`);
    }
}">
    <div class="space-y-4">
        <x-card>
            <x-slot name="title">Generate Report</x-slot>
            <div class="flex items-center space-x-4">
                <x-select x-model="selectedReport" class="w-[240px]">
                    <template x-for="type in reportTypes" :key="type">
                        <option :value="type" x-text="type"></option>
                    </template>
                </x-select>
                <x-button @click="handleGenerateReport" variant="primary">Generate Report</x-button>
            </div>
        </x-card>
        
        <x-card>
            <div class="p-6 flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="text-xl font-semibold" x-text="`${selectedReport} Report`"></h3>
            </div>
            <div class="relative w-full overflow-auto">
                <table class="w-full caption-bottom text-sm">
                    <thead class="[&_tr]:border-b">
                        <tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Metric</th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Value</th>
                        </tr>
                    </thead>
                    <tbody class="[&_tr:last-child]:border-0">
                        <template x-for="row in reportData[selectedReport]" :key="row.id">
                            <tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                                <td class="p-4 align-middle" x-text="row.metric"></td>
                                <td class="p-4 align-middle" x-text="row.value"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <div class="flex justify-end space-x-2 mt-4">
                <x-button @click="handleDownloadReport" variant="outline">
                    <x-icon name="download" class="mr-2 h-4 w-4"></x-icon>
                    Download
                </x-button>
                <x-button @click="handlePrintReport" variant="outline">
                    <x-icon name="printer" class="mr-2 h-4 w-4"></x-icon>
                    Print
                </x-button>
            </div>
        </x-card>
    </div>
</div>

