<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>

        @fonts

        <!-- Styles / Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif

        <style>
            .custom-scrollbar::-webkit-scrollbar {
                width: 6px;
            }
            .custom-scrollbar::-webkit-scrollbar-track {
                background: #0f0f12;
            }
            .custom-scrollbar::-webkit-scrollbar-thumb {
                background: #27272a;
                border-radius: 9999px;
            }
            .custom-scrollbar::-webkit-scrollbar-thumb:hover {
                background: #3f3f46;
            }
        </style>
    </head>
    <body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] flex p-6 lg:p-8 items-center lg:justify-center min-h-screen flex-col">
        <div class="min-h-screen bg-[#0f0f12] text-gray-100 p-4 md:p-8 font-sans antialiased selection:bg-purple-500 selection:text-white">
            <!-- Banner de Alerta Dinâmico do Blade -->
            @if (session('status_cadastro'))
                <div class="mb-6 p-4 rounded-xl border bg-purple-500/10 border-purple-500/30 text-purple-300 flex items-center justify-between shadow-lg shadow-purple-500/5 animate-pulse">
                    <div class="flex items-center gap-3 text-sm">
                        <span class="text-xl">📡</span>
                        <div>
                            <span class="font-bold uppercase tracking-wider text-xs block text-purple-400">Ação concluída</span>
                            {{ session('status_cadastro')['message'] }}
                        </div>
                    </div>
                    <!-- Botão para fechar o banner com um clique -->
                    <button onclick="this.parentElement.remove()" class="text-gray-400 hover:text-white text-xs font-bold px-2 py-1 cursor-pointer">✕</button>
                </div>
            @endif

            <!-- Header Principal -->
            <header class="mb-8 border-b border-gray-800 pb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight bg-linear-to-r from-purple-400 via-pink-500 to-red-500 bg-clip-text text-transparent">
                        Arcade Management Console
                    </h1>
                    <p class="text-sm text-gray-400 mt-1">
                        Monitoramento de hardware, clientes e transações criptográficas em tempo real.
                    </p>
                </div>
                <div class="flex items-center gap-2 bg-gray-900 border border-gray-800 px-4 py-2 rounded-xl self-start md:self-auto">
                    <span class="relative flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-3 rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                    </span>
                    <span class="text-xs font-mono font-bold tracking-wider text-green-400 uppercase">
                        Rede: Online (HTTPS)
                    </span>
                </div>
            </header>

            <!-- Grid Layout Responsivo (Duas Colunas Principais) -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                
                <!-- COLUNA 1: Máquinas Disponíveis (Fliperamas) -->
                <section class="xl:col-span-1 space-y-4">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold tracking-wide text-gray-200 uppercase flex items-center gap-2">
                            🕹️ Máquinas Disponíveis
                        </h2>
                        <span class="text-xs bg-gray-800 border border-gray-700 px-1.5 py-1 rounded-md font-mono text-gray-400">
                            {{ count($machines) }} total
                        </span>
                    </div>

                    <div class="space-y-4 max-h-[calc(100vh-220px)] overflow-y-auto pr-2 custom-scrollbar">
                        @foreach ($machines as $machine)
                            <div class="bg-[#16161a] border border-gray-800/80 rounded-2xl p-5 shadow-xl transition-all duration-300 hover:border-purple-500/40 hover:shadow-purple-500/5 group relative overflow-hidden">
                                <!-- Efeito de Linha Neon para Máquinas Ativas -->
                                <div class="absolute top-0 left-0 h-1 w-full @if($machine->is_online) == 'active') bg-linear-to-r from-purple-500 to-pink-500 @else bg-gray-700 @endif"></div>
                                
                                <div class="flex items-start justify-between gap-4 mt-1">
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-100 group-hover:text-purple-400 transition-colors">
                                            {{ $machine->name }}
                                        </h3>
                                        <p class="text-gray-400 flex font-mono text-xs items-center gap-1.5 mt-1">
                                            ID: {{ $machine->api_token }}
                                        </p>
                                    </div>
                                    
                                    <!-- Badges Dinâmicos de Status -->
                                    @if($machine->is_online)
                                        <span class="text-xs px-2.5 py-1 rounded-full font-bold uppercase tracking-wider bg-green-500/10 text-green-400 border border-green-500/20">
                                            Ativa
                                        </span>
                                    @else
                                        <span class="text-xs px-2.5 py-1 rounded-full font-bold uppercase tracking-wider bg-yellow-500/10 text-yellow-400 border border-yellow-500/20">
                                            Manutenção
                                        </span>
                                    @endif
                                </div>

                                <!-- Detalhes de Hardware -->
                                <div class="mt-4 pt-4 border-t border-gray-800/60 flex items-center justify-between text-xs text-gray-500 font-mono">
                                    <span>Custo/Ficha: <strong class="text-purple-400 font-sans text-sm">R$ {{ number_format($machine->cost_per_play ?? 2.00, 2, ',', '.') }}</strong></span>
                                    <span>Manutenção: {{ is_numeric($machine->last_maintenance ?? null) || is_string($machine->last_maintenance ?? null) ? \Carbon\Carbon::parse($machine->last_maintenance)->format('d/m/Y') : 'N/A' }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <!-- COLUNA 2 e 3: Clientes e Seus Cartões NFC Protegidos -->
                <section class="xl:col-span-2 space-y-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold tracking-wide text-gray-200 uppercase flex items-center gap-2">
                            👥 Titulares & Cartões Associados
                        </h2>
                    </div>

                    <button type="button" data-modal-open="customer-registration-modal"
                            class="rounded-lg cursor-pointer bg-purple-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-purple-500">
                        Cadastrar cliente
                    </button>

                    <dialog id="customer-registration-modal" aria-labelledby="customer-registration-title"
                            @if ($errors->any() && old('_form') === 'create-customer') data-open-on-load @endif
                            class="fixed left-1/2 top-1/2 m-0 max-h-[calc(100%-2rem)] w-[calc(100%-2rem)] max-w-2xl -translate-x-1/2 -translate-y-1/2 overflow-y-auto rounded-2xl border border-gray-800 bg-[#131316] p-0 text-gray-100 shadow-2xl backdrop:bg-black/75 backdrop:backdrop-blur-sm">
                        <form action="{{ route('customers.store') }}" method="POST" class="space-y-5 p-6">
                            @csrf
                            <input type="hidden" name="_form" value="create-customer">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 id="customer-registration-title" class="text-xl font-semibold text-gray-100">Cadastrar cliente</h3>
                                    <p class="mt-1 text-sm text-gray-400">Preencha os dados para adicionar um novo titular.</p>
                                </div>
                                <button type="button" data-modal-close
                                        class="rounded-lg px-2 py-1 text-gray-400 transition hover:bg-gray-800 hover:text-white"
                                        aria-label="Fechar cadastro">✕</button>
                            </div>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label for="name" class="mb-1 block text-xs text-gray-400">Nome *</label>
                                    <input id="name" name="name" value="{{ old('name') }}" required maxlength="255"
                                           class="w-full rounded-lg border border-gray-700 bg-gray-900 px-3 py-2 text-sm text-gray-100">
                                    @error('name') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="document" class="mb-1 block text-xs text-gray-400">Documento *</label>
                                    <input id="document" name="document" value="{{ old('document') }}" required maxlength="255"
                                           class="w-full rounded-lg border border-gray-700 bg-gray-900 px-3 py-2 text-sm text-gray-100">
                                    @error('document') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="email" class="mb-1 block text-xs text-gray-400">E-mail</label>
                                    <input id="email" name="email" type="email" value="{{ old('email') }}" maxlength="255"
                                           class="w-full rounded-lg border border-gray-700 bg-gray-900 px-3 py-2 text-sm text-gray-100">
                                    @error('email') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="phone" class="mb-1 block text-xs text-gray-400">Telefone</label>
                                    <input id="phone" name="phone" value="{{ old('phone') }}" maxlength="255"
                                           class="w-full rounded-lg border border-gray-700 bg-gray-900 px-3 py-2 text-sm text-gray-100">
                                    @error('phone') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="flex justify-end gap-3 border-t border-gray-800 pt-4">
                                <button type="button" data-modal-close
                                        class="rounded-lg border border-gray-700 cursor-pointer px-4 py-2 text-sm font-medium text-gray-300 transition hover:bg-gray-800">
                                    Cancelar
                                </button>
                                <button type="submit" class="rounded-lg cursor-pointer bg-purple-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-purple-500">
                                    Cadastrar cliente
                                </button>
                            </div>
                        </form>
                    </dialog>

                    <div class="space-y-6 max-h-[calc(100vh-220px)] overflow-y-auto pr-2 custom-scrollbar">
                        @forelse ($customers as $customer)
                            <div class="bg-[#131316] border border-gray-800 rounded-2xl p-6 shadow-2xl space-y-6">

                                <!-- Header do Perfil do Cliente -->
                                <div class="relative flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-800 pb-4 pt-6 sm:pt-2">
                                    
                                    <form action="{{ route('cadastro-nfc', $customer->id) }}" method="POST" class="absolute top-0 right-0">
                                        @csrf
                                        <button type="submit"
                                                title="Vincular Novo Cartão"
                                                aria-label="Vincular novo cartão a {{ $customer->name }}"
                                                class="cursor-pointer text-[11px] bg-purple-600/10 text-purple-400 border border-purple-500/20 hover:bg-purple-600 hover:text-white px-2.5 py-1 rounded-lg font-medium transition-all duration-200 flex items-center gap-1 shadow-xs hover:shadow-purple-500/20">
                                            <span>➕ <span sr-only="Vincular cartão NFC"></span></span>
                                        </button>
                                    </form>

                                    <div class="flex items-center gap-4 mt-4 sm:mt-0">
                                        <div class="h-12 w-12 rounded-xl bg-linear-to-br from-purple-600 to-indigo-600 flex items-center justify-center font-bold text-lg text-white shadow-lg shadow-indigo-600/20">
                                            {{ strtoupper(substr($customer->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <h3 class="text-xl font-bold text-gray-100 tracking-tight">{{ $customer->name }}</h3>
                                            <p class="text-sm text-purple-400/90 font-medium">Titular do cartão</p>
                                        </div>
                                    </div>

                                    <button type="button" data-modal-open="customer-edit-{{ $customer->id }}"
                                            class="rounded-lg border border-gray-700 px-3 py-2 text-xs font-medium text-gray-300 transition hover:bg-gray-800">
                                        Editar cliente
                                    </button>

                                    <!-- Contatos Rápidos -->
                                    <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-400 font-mono sm:text-right sm:justify-end sm:pr-32">
                                        <span class="block">📧 {{ $customer->email }}</span>
                                        <span class="block">📞 {{ $customer->phone }}</span>
                                    </div>
                                </div>

                                <dialog id="customer-edit-{{ $customer->id }}" aria-labelledby="customer-edit-title-{{ $customer->id }}"
                                        @if ($errors->any() && old('_form') === 'edit-customer-' . $customer->id) data-open-on-load @endif
                                        class="fixed left-1/2 top-1/2 m-0 max-h-[calc(100%-2rem)] w-[calc(100%-2rem)] max-w-2xl -translate-x-1/2 -translate-y-1/2 overflow-y-auto rounded-2xl border border-gray-800 bg-[#131316] p-0 text-gray-100 shadow-2xl backdrop:bg-black/75 backdrop:backdrop-blur-sm">
                                    <form action="{{ route('customers.update', $customer) }}" method="POST" class="space-y-5 p-6">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="_form" value="edit-customer-{{ $customer->id }}">
                                        <div class="flex items-center justify-between gap-4">
                                            <h3 id="customer-edit-title-{{ $customer->id }}" class="text-xl font-semibold">Editar cliente</h3>
                                            <button type="button" data-modal-close class="rounded-lg px-2 py-1 text-gray-400 hover:bg-gray-800" aria-label="Fechar edição">✕</button>
                                        </div>
                                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                            <div>
                                                <label for="edit-name-{{ $customer->id }}" class="mb-1 block text-xs text-gray-400">Nome *</label>
                                                <input id="edit-name-{{ $customer->id }}" name="name" value="{{ old('_form') === 'edit-customer-' . $customer->id ? old('name') : $customer->name }}" required maxlength="255"
                                                       class="w-full rounded-lg border border-gray-700 bg-gray-900 px-3 py-2 text-sm text-gray-100">
                                                @if (old('_form') === 'edit-customer-' . $customer->id) @error('name') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror @endif
                                            </div>
                                            <div>
                                                <label for="edit-document-{{ $customer->id }}" class="mb-1 block text-xs text-gray-400">Documento *</label>
                                                <input id="edit-document-{{ $customer->id }}" name="document" value="{{ old('_form') === 'edit-customer-' . $customer->id ? old('document') : $customer->document }}" required maxlength="255"
                                                       class="w-full rounded-lg border border-gray-700 bg-gray-900 px-3 py-2 text-sm text-gray-100">
                                                @if (old('_form') === 'edit-customer-' . $customer->id) @error('document') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror @endif
                                            </div>
                                            <div>
                                                <label for="edit-email-{{ $customer->id }}" class="mb-1 block text-xs text-gray-400">E-mail</label>
                                                <input id="edit-email-{{ $customer->id }}" name="email" type="email" value="{{ old('_form') === 'edit-customer-' . $customer->id ? old('email') : $customer->email }}" maxlength="255"
                                                       class="w-full rounded-lg border border-gray-700 bg-gray-900 px-3 py-2 text-sm text-gray-100">
                                                @if (old('_form') === 'edit-customer-' . $customer->id) @error('email') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror @endif
                                            </div>
                                            <div>
                                                <label for="edit-phone-{{ $customer->id }}" class="mb-1 block text-xs text-gray-400">Telefone</label>
                                                <input id="edit-phone-{{ $customer->id }}" name="phone" value="{{ old('_form') === 'edit-customer-' . $customer->id ? old('phone') : $customer->phone }}" maxlength="255"
                                                       class="w-full rounded-lg border border-gray-700 bg-gray-900 px-3 py-2 text-sm text-gray-100">
                                                @if (old('_form') === 'edit-customer-' . $customer->id) @error('phone') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror @endif
                                            </div>
                                        </div>
                                        <div class="flex justify-end gap-3 border-t border-gray-800 pt-4">
                                            <button type="button" data-modal-close class="rounded-lg border border-gray-700 px-4 py-2 text-sm text-gray-300 hover:bg-gray-800">Cancelar</button>
                                            <button type="submit" class="rounded-lg bg-purple-600 px-4 py-2 text-sm font-semibold text-white hover:bg-purple-500">Salvar alterações</button>
                                        </div>
                                    </form>
                                </dialog>

                                <!-- Listagem Exclusiva de Cartões NFC Estilizados -->
                                <div>
                                    <h4 class="text-xs font-mono font-bold tracking-widest text-gray-500 uppercase mb-4">
                                        Cartões Registrados ({{ count($customer->cards ?? []) }})
                                    </h4>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        @foreach ($customer->cards ?? [] as $card)
                                            <!-- Card NFC com Layout de Cartão Físico -->
                                            <div class="space-y-3">
                                            <div class="relative bg-linear-to-br from-[#1c1c24] via-[#16161b] to-[#121217] border border-gray-800 rounded-xl p-5 shadow-md flex flex-col justify-between min-h-44 overflow-hidden group hover:border-pink-500/30 transition-all duration-300">

                                                <!-- Marca D'água Decorativa de Fundo -->
                                                <div class="absolute -right-4 -bottom-4 text-gray-800/10 text-7xl font-bold select-none group-hover:text-pink-500/5 transition-colors">NFC</div>

                                                <!-- Linha do Topo do Cartão -->
                                                <div class="flex items-center justify-between relative z-10">
                                                    <!-- Chip Simulado do Cartão / Ícone NFC -->
                                                    <div class="flex items-center gap-2">
                                                        <div class="w-8 h-6 bg-linear-to-r from-yellow-500/40 to-yellow-600/20 rounded border border-yellow-500/30 flex items-center justify-center">
                                                            <span class="text-[8px] font-mono text-yellow-300 opacity-60">CHIP</span>
                                                        </div>
                                                        <span class="text-[10px] font-mono tracking-widest text-gray-400 uppercase">
                                                            MIFARE S3
                                                        </span>
                                                    </div>
                                                    <!-- Status Badge do Cartão -->
                                                    <span class="text-[10px] font-mono px-2 py-0.5 rounded font-bold uppercase tracking-wider @if($card->status === \App\Enums\CardType::ACTIVE) bg-green-500/10 text-green-400 border border-green-500/20 @else bg-red-500/10 text-red-400 border border-red-500/20 @endif">
                                                        {{ $card->status->getLabel() }}
                                                    </span>
                                                </div>

                                                <!-- UID formatado em Bloco Central -->
                                                <div class="my-4 relative z-10">
                                                    <span class="text-xs text-gray-500 font-mono block tracking-wider uppercase">
                                                        UID
                                                    </span>

                                                    {{ strtoupper(implode(' ', str_split($card->uid ?? '00000000', 4))) }}

                                                    <span class="text-xs text-gray-500 font-mono block tracking-wider uppercase">
                                                        Saldo Disponível
                                                    </span>
                                                    <span class="text-lg font-bold text-green-400">
                                                        R$ {{ number_format($card->balance ?? 0.00, 2, ',', '.') }}
                                                    </span>
                                                </div>
                                            </div>

                                            <details class="rounded-lg border border-gray-800/80 bg-gray-950/40 px-3 py-2">
                                                <summary class="cursor-pointer select-none text-xs font-medium text-gray-400 hover:text-gray-200">
                                                    Gerenciar cartão
                                                </summary>
                                                <div class="mt-3 space-y-3">
                                                    <form action="{{ route('cards.status', $card) }}" method="POST" class="flex items-center gap-2">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="_form" value="card-status-{{ $card->id }}">
                                                        <label for="status-{{ $card->id }}" class="sr-only">Status do cartão</label>
                                                        <select id="status-{{ $card->id }}" name="status" class="min-w-0 flex-1 rounded-md border border-gray-700 bg-gray-900 px-2 py-1.5 text-xs text-gray-200">
                                                            <option value="active" @selected(old('_form') === 'card-status-' . $card->id ? old('status') === 'active' : $card->status === \App\Enums\CardType::ACTIVE)>Ativo</option>
                                                            <option value="blocked" @selected(old('_form') === 'card-status-' . $card->id ? old('status') === 'blocked' : $card->status === \App\Enums\CardType::BLOCKED)>Bloqueado</option>
                                                            <option value="suspended" @selected(old('_form') === 'card-status-' . $card->id ? old('status') === 'suspended' : $card->status === \App\Enums\CardType::SUSPENDED)>Suspenso</option>
                                                        </select>
                                                        <button type="submit" class="rounded-md border border-gray-700 px-2 py-1.5 text-xs text-gray-300 hover:bg-gray-800">Salvar</button>
                                                    </form>
                                                    @if (old('_form') === 'card-status-' . $card->id) @error('status') <p class="text-xs text-red-400">{{ $message }}</p> @enderror @endif

                                                    <form action="{{ route('cards.credit', $card) }}" method="POST" class="flex items-center gap-2">
                                                        @csrf
                                                        <input type="hidden" name="_form" value="credit-card-{{ $card->id }}">
                                                        <label for="amount-{{ $card->id }}" class="shrink-0 text-xs text-gray-500">Adicionar R$</label>
                                                        <input id="amount-{{ $card->id }}" name="amount" type="number" min="0.01" max="999999.99" step="0.01" value="{{ old('_form') === 'credit-card-' . $card->id ? old('amount') : '' }}" required
                                                               class="min-w-0 flex-1 rounded-md border border-gray-700 bg-gray-900 px-2 py-1.5 text-xs text-gray-200">
                                                        <button type="submit" class="rounded-md bg-green-800/80 px-2 py-1.5 text-xs text-green-100 hover:bg-green-700">Adicionar</button>
                                                    </form>
                                                    @if (old('_form') === 'credit-card-' . $card->id) @error('amount') <p class="text-xs text-red-400">{{ $message }}</p> @enderror @endif
                                                </div>
                                            </details>
                                            </div>

                                        @empty
                                            <p class="rounded-xl border border-dashed border-gray-700 p-6 text-center text-sm text-gray-400">
                                                Nenhum cliente cadastrado ainda.
                                            </p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>
        </div>
        <script>
            document.querySelectorAll('[data-modal-open]').forEach((button) => {
                button.addEventListener('click', () => {
                    document.getElementById(button.dataset.modalOpen)?.showModal();
                });
            });

            document.querySelectorAll('dialog [data-modal-close]').forEach((button) => {
                button.addEventListener('click', () => {
                    button.closest('dialog')?.close();
                });
            });

            document.querySelectorAll('dialog[data-open-on-load]').forEach((dialog) => {
                dialog.showModal();
            });
        </script>
    </body>
</html>
