<div x-data="{
    transactions: [
        {
            id: '1',
            name: 'Alice Johnson',
            email: 'alice@example.com',
            amount: '+$350.00',
            status: 'success',
            date: '2023-07-20',
            avatar: 'https://hebbkx1anhila5yf.public.blob.vercel-storage.com/375238645_11475210.jpg-lU8bOe6TLt5Rv51hgjg8NT8PsDBmvN.jpeg'
        },
        {
            id: '2',
            name: 'Bob Smith',
            email: 'bob@example.com',
            amount: '-$120.50',
            status: 'pending',
            date: '2023-07-19',
            avatar: 'https://hebbkx1anhila5yf.public.blob.vercel-storage.com/375238208_11475222.jpg-poEIzVHAGiIfMFQ7EiF8PUG1u0Zkzz.jpeg'
        },
        {
            id: '3',
            name: 'Charlie Brown',
            email: 'charlie@example.com',
            amount: '+$1,000.00',
            status: 'success',
            date: '2023-07-18',
            avatar: 'https://hebbkx1anhila5yf.public.blob.vercel-storage.com/dd.jpg-4MCwPC2Bec6Ume26Yo1kao3CnONxDg.jpeg'
        },
        {
            id: '4',
            name: 'Diana Martinez',
            email: 'diana@example.com',
            amount: '-$50.75',
            status: 'failed',
            date: '2023-07-17',
            avatar: 'https://hebbkx1anhila5yf.public.blob.vercel-storage.com/9334178.jpg-Y74tW6XFO68g7N36SE5MSNDNVKLQ08.jpeg'
        },
        {
            id: '5',
            name: 'Ethan Williams',
            email: 'ethan@example.com',
            amount: '+$720.00',
            status: 'success',
            date: '2023-07-16',
            avatar: 'https://hebbkx1anhila5yf.public.blob.vercel-storage.com/5295.jpg-fLw0wGGZp8wuTzU5dnyfjZDwAHN98a.jpeg'
        }
    ]
}">
    <div class="space-y-4">
        <template x-for="transaction in transactions" :key="transaction.id">
            <div class="bg-card rounded-lg border border-border shadow-sm p-4">
                <div class="flex items-center p-0">
                    <div class="h-10 w-10 rounded-full overflow-hidden">
                        <img :src="transaction.avatar" :alt="transaction.name" class="h-full w-full object-cover">
                    </div>
                    <div class="ml-4 flex-1 space-y-1">
                        <p class="text-sm font-medium leading-none" x-text="transaction.name"></p>
                        <p class="text-xs text-muted-foreground" x-text="transaction.email"></p>
                    </div>
                    <div class="ml-auto text-right">
                        <p class="text-sm font-medium" :class="transaction.amount.startsWith('+') ? 'text-green-500' : 'text-red-500'" x-text="transaction.amount"></p>
                        <p class="text-xs text-muted-foreground" x-text="transaction.date"></p>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>

