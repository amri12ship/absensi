@extends('layouts.app')

@section('title', 'Kalender Absensi')

@section('content')
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('employee.history.calendar') }}" class="row g-2 align-items-end">
                <div class="col-sm-4">
                    <label class="form-label small text-muted mb-1">Bulan</label>
                    <select name="bulan" class="form-select">
                        @foreach (range(1, 12) as $m)
                            <option value="{{ $m }}" {{ $month === $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-4">
                    <label class="form-label small text-muted mb-1">Tahun</label>
                    <select name="tahun" class="form-select">
                        @foreach (range(now()->year, now()->year - 2) as $y)
                            <option value="{{ $y }}" {{ $year === $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-eye me-1"></i> Tampilkan</button>
                    <a href="{{ route('employee.history.index') }}" class="btn btn-outline-secondary">Riwayat</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-calendar-month me-2"></i>{{ $startOfMonth->translatedFormat('F Y') }}
        </div>
        <div class="table-responsive">
            <table class="table table-bordered mb-0 text-center">
                <thead>
                    <tr>
                        <th>Senin</th><th>Selasa</th><th>Rabu</th><th>Kamis</th>
                        <th>Jumat</th><th>Sabtu</th><th>Minggu</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $firstDOW = $startOfMonth->isoWeekday(); // 1-7 Senin..Minggu
                        $blank = $firstDOW - 1;
                        $cells = [];
                        for ($i = 0; $i < $blank; $i++) $cells[] = null;
                        foreach ($days as $day => $info) $cells[] = $info;
                        while (count($cells) % 7 !== 0) $cells[] = null;
                    @endphp
                    @for ($i = 0; $i < count($cells); $i += 7)
                        <tr>
                            @for ($j = 0; $j < 7; $j++)
                                @php $cell = $cells[$i + $j] ?? null; @endphp
                                <td class="{{ $cell && $cell['date']->isToday() ? 'bg-primary bg-opacity-10' : '' }}" style="height:90px;vertical-align:top;min-width:110px">
                                    @if ($cell)
                                        <div class="d-flex justify-content-between align-items-start">
                                            <strong>{{ $cell['date']->day }}</strong>
                                            @if ($cell['isToday'])
                                                <span class="badge bg-primary">Hari ini</span>
                                            @endif
                                        </div>
                                        @if ($cell['holiday'])
                                            <div>
                                                <span class="badge bg-secondary">Libur</span>
                                                <div class="small text-muted">{{ $cell['holiday']->name }}</div>
                                            </div>
                                        @elseif ($cell['attendance'])
                                            <div class="mt-1">
                                                <span class="badge {{ $cell['attendance']->statusBadge() }}">{{ $cell['attendance']->statusLabel() }}</span>
                                                <div class="small mt-1">
                                                    <div>Masuk: {{ $cell['attendance']->check_in ? \Carbon\Carbon::parse($cell['attendance']->check_in)->format('H:i') : '-' }}</div>
                                                    <div>Keluar: {{ $cell['attendance']->check_out ? \Carbon\Carbon::parse($cell['attendance']->check_out)->format('H:i') : '-' }}</div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="small text-muted mt-2">-</div>
                                        @endif
                                    @endif
                                </td>
                            @endfor
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>
    </div>
@endsection