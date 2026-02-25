<table>
    <thead>
    <tr>
        <th>Group Name</th>
        <th>Researchers</th>
        <th>Program</th>
        <th>Subject Name</th>
        <th>Subject Instructor</th>
        <th>Assigned Personnel</th>
        <th>Base Fee</th>
        <th>Honorarium</th>
        <th>Total Fee</th>
    </tr>
    </thead>
    <tbody>
    @foreach($groups as $group)
        <tr>
            <td>{{ $group->name }}</td>
            <td>
                @foreach($group->members as $member)
                    {{ $member->first_name }} {{ $member->last_name }} ({{ $member->student_number }})@if(!$loop->last)<br>@endif
                @endforeach
            </td>
            <td>{{ $group->section->program->name ?? '' }}</td>
            <td>{{ $group->section->name ?? '' }}</td>
            <td>{{ optional($group->section->instructor)->first_name }} {{ optional($group->section->instructor)->last_name }}</td>
            <td>
                @foreach($group->personnel as $personnel)
                    {{ optional($personnel->role)->getLabel() }}: {{ optional($personnel->instructor)->first_name }} {{ optional($personnel->instructor)->last_name }}@if(!$loop->last)<br>@endif
                @endforeach
            </td>
            <td>{{ $group->fee ? number_format($group->fee->base_fee, 2) : '0.00' }}</td>
            <td>{{ $group->fee ? number_format($group->fee->honorarium_total, 2) : '0.00' }}</td>
            <td>{{ $group->fee ? number_format($group->fee->total_merger_amount, 2) : '0.00' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>