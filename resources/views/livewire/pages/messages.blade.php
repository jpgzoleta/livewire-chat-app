<?php
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Services\UserService;
use App\Services\ConversationService;
use App\Services\MessageService;
use Livewire\Attributes\Locked;

new #[Layout('layouts.app')] #[Title('Messages')] class extends Component {
    #[Locked]
    public $conversations = [];
    public $conversationMessages = [];
    #[Locked]
    public $userInfo;
    public $isModalOpen = false;
    public $searchValue = '';
    public $messageBody = '';
    public $users = [];
    #[Locked]
    public $activeConversation;

    public function mount()
    {
        $this->userInfo = auth()->user();
        $this->conversations = ConversationService::getUserConversations();
    }

    public function setIsModalOpen($isOpen)
    {
        $this->isModalOpen = $isOpen;
    }

    public function addMessage($data)
    {
        // dd($data);
        $this->conversationMessages[] = json_decode(json_encode($data));
    }

    public function clearSearchValue()
    {
        $this->reset('searchValue');
    }

    public function createPrivateConversation($toChatUserId)
    {
    }

    public function setActiveConversation($conversation)
    {
        $toJoinConversation = json_decode($conversation);
        $this->dispatch('conversation-select', toJoinId: $toJoinConversation->id, toLeaveId: $this->activeConversation ? $this->activeConversation->id : null);
        $this->activeConversation = $toJoinConversation;

        $this->conversationMessages = MessageService::getConversationMessages($this->activeConversation->id);
        // dd($this->conversationMessages);
    }

    public function sendMessage()
    {
        $this->dispatch('message-send', conversation: $this->activeConversation->id, sender: $this->userInfo, body: $this->messageBody);
        $this->reset('messageBody');
    }

    public function getPrivateConversationName($members)
    {
        $userToChat;
        foreach ($members as $member) {
            if ($member->id != auth()->user()->id) {
                $userToChat = $member->name;
                break;
            }
        }
        return $userToChat;
    }

    public function updated($name, $value)
    {
        if ($name == 'searchValue') {
            $this->users = UserService::getUsers($value);
        }
    }
}; ?>

<div class="h-full max-h-full py-8">
    <x-modal :show="$isModalOpen" name="test" focusable>
        <div class="flex flex-col gap-4 p-4">
            <div class="flex flex-col gap-1">
                <div class="flex items-center justify-between gap-4">
                    <p class="text-xl font-semibold dark:text-gray-100">Create Conversation</p>
                    <button type="button" wire:click="$set('isModalOpen', false)"
                        class="flex items-center justify-center">
                        <i class="ph-bold ph-x text-[16px] dark:text-gray-100"></i>
                    </button>
                </div>
                <p class="text-sm dark:text-gray-400">Start a conversation with another user</p>
            </div>
            <div class="flex flex-col gap-4">
                <x-text-input type="text" wire:model.live.debounce="searchValue" placeholder="Enter name or email" />
                <ul class="flex flex-col">
                    @foreach ($this->users as $user)
                        <li wire:key='{{ $user->id }}'
                            class="flex items-center gap-3 px-3 py-2 rounded-lg cursor-pointer group hover:dark:bg-gray-700">
                            <div class="flex-shrink-0 aspect-square">
                                <img src="{{ asset('images/placeholders/Portrait_Placeholder.png') }}" alt="user image"
                                    class="rounded-full object-cover w-[42px] h-[42px] bg-gray-300">
                            </div>
                            <div class="flex flex-col w-full">
                                <p class="font-medium dark:text-gray-100">{{ $user->name }}</p>
                                <p class="text-xs dark:text-gray-400">{{ $user->email }}</p>
                            </div>
                            <span class="hidden group-hover:block">
                                <i class="ph-fill ph-chat-text text-[20px] drop-shadow-md dark:text-gray-500"></i>
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </x-modal>
    <div class="flex h-full max-h-full gap-8 mx-auto max-w-7xl sm:px-6 lg:px-8">
        {{-- section 1 - conversations --}}
        <div class="flex flex-col gap-4 w-full h-full max-w-[320px]">
            {{-- list --}}
            <div class="flex items-center justify-between gap-4">
                <p class="text-lg font-semibold dark:text-gray-100">Select Conversation</p>
                <x-secondary-button wire:click="$set('isModalOpen', true)" class="flex items-center gap-3">
                    <i class="ph-bold ph-plus text-[12px] "></i>
                </x-secondary-button>
            </div>
            <ul class="flex flex-col gap-3 pb-4">
                {{-- convo item --}}
                @foreach ($conversations as $conversation)
                    <li wire:click="setActiveConversation('{{ json_encode($conversation) }}')"
                        wire:key='{{ $conversation->id }}' @class([
                            'flex gap-4 px-3 py-2 -ml-3 rounded-lg cursor-pointer lg:-ml-3',
                            'dark:hover:bg-gray-800 hover:bg-gray-300' => $activeConversation
                                ? $activeConversation->id != $conversation->id
                                : true,
                            'bg-gray-300 dark:bg-gray-700 transition-colors' => $activeConversation
                                ? $activeConversation->id == $conversation->id
                                : false,
                        ])>
                        <div class="flex-shrink-0 aspect-square">
                            <img src="{{ asset('images/placeholders/Portrait_Placeholder.png') }}" alt="user image"
                                class="rounded-full object-cover w-[42px] h-[42px] bg-gray-300">
                        </div>
                        <div class="flex flex-col">
                            <p class="font-medium text-gray-900 dark:text-gray-100">
                                {{ $conversation->type == 'private' ? $this->getPrivateConversationName($conversation->members) : $conversation->name }}
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">subtitle</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
        {{-- section 2 - chatbox --}}
        <div class="grid grid-cols-1 grid-rows-[auto_1fr_auto] w-full h-full ">
            {{-- header --}}
            <div class="flex items-center justify-between pb-4 border-b border-gray-300 dark:border-gray-700">
                <div class="flex items-center gap-4">
                    <p class="text-2xl font-semibold dark:text-gray-100">
                        {{ $activeConversation ? $this->getPrivateConversationName($activeConversation->members) : 'Select a Conversation' }}
                    </p>
                </div>
            </div>
            {{-- {{ $conversationMessages }} --}}
            <ul class="flex flex-col justify-end gap-4 py-4" id="chat-box">
                @foreach ($this->conversationMessages as $message)
                    @if ($message->sender->id == $userInfo->id)
                        <li class="flex gap-4 max-w-[60%] self-end w-fit">
                            <div class="flex flex-col items-end gap-2">
                                <div class="p-3 text-white bg-indigo-500 rounded-lg w-fit">
                                    <p class="text-white">{{ $message->body }}</p>
                                </div>
                                <p class="text-xs text-gray-500 whitespace-nowrap">mm-dd-yyyy : hh:ss am</p>
                            </div>
                            <div class="aspect-square flex-shrink-0 w-[42px] h-[42px]">
                                <img src="{{ asset('images/placeholders/Portrait_Placeholder.png') }}" alt="user image"
                                    class="rounded-full w-[42px] h-[42px] bg-gray-300 object-cover">
                            </div>
                        </li>
                    @else
                        <li class="flex flex-row-reverse gap-4 max-w-[60%] w-fit">
                            <div class="flex flex-col gap-2">
                                <div class="p-3 bg-gray-300 rounded-lg dark:bg-gray-700 dark:text-gray-100 w-fit">
                                    <p>{{ $message->body }}</p>
                                </div>
                                <p class="text-xs text-gray-500 whitespace-nowrap">mm-dd-yyyy : hh:ss am</p>
                            </div>
                            <div class="w-[42px] h-[42px] flex-shrink-0">
                                <img src="{{ asset('images/placeholders/Portrait_Placeholder.png') }}" alt="user image"
                                    class="rounded-full w-[42px] object-cover h-[42px] bg-gray-300">
                            </div>
                        </li>
                    @endif
                @endforeach
            </ul>
            {{-- input --}}
            <div
                class="sticky bottom-[1rem] flex items-end gap-4 p-4 bg-gray-800 border-t border-gray-300 rounded-lg dark:border-gray-700">
                <x-text-area wire:model="messageBody" id="chat-input" placeholder='Enter message here' class="w-full" />
                <x-primary-button id="send-button" wire:click='sendMessage()'>
                    <i class="text-3xl ph-fill ph-paper-plane-right"></i>
                </x-primary-button>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('livewire:initialized', () => {
            window.socket.on("chat:receive", (data) => {
                // Handle received chat messages here.
                @this.addMessage(data)
            });

            @this.on('message-send', (message) => {
                window.socket.emit("chat:send", message)
            })

            @this.on('conversation-select', ({
                toJoinId,
                toLeaveId
            }) => {
                window.socket.emit('conversation:join', {
                    toJoinId,
                    toLeaveId
                })
            });
        });
    </script>
</div>
