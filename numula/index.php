<?php

$score = 'NScore';
$note = 'NNote';
$measure = 'NMeasure';
$mns = 'NDF';

echo "
<div style=\"max-width: 700px; font-size:14px; font-family:Trebuchet MS; line-height:1.4\" align=justify>
<a href=outline.html>Outline</a>
<center>
<h1>Describing musical performance nuance</h1>

<p>
David P. Anderson
<p>
June 1, 2025
</center>

<h2>Abstract</h2>

We present a model, Nuance Description Framework (NDF)
for describing performance nuance
(timing, dynamics, articulation, and pedaling)
in notated keyboard music.
$mns can concisely express nuance
at a level of detail that closely approximates typical human performance.
This capability creates many musical possibilities.
Some of these involve human-created 'nuance specifications';
this has application in composition, virtual performance,
and performance pedagogy.
We describe these applications,
and the possible interfaces for creating and editing nuance specifications.
It is also possible to 'infer' nuance descriptions
from recorded human performances;
this enables studies of performance practice.

<p>

<a name=intro></a>
<h2>1. Introduction</h2>
<h3>1.1  Performance nuance</h3>
<p>
This paper is concerned with 'performance nuance' in notated music,
by which we mean the differences between music as notated
and music as performed.
We focus on keyboard instruments such as piano.
In this context, nuance has several components:
<p>
<li> Timing: tempo, tempo variation, rubato, pauses, rolled chords
and other time-shifting of notes.
<li> Dynamics: crescendos and diminuendos, accents, voicing, etc.
<li> Articulation: legato, staccato, portamento, etc.
<li> The use of pedals (sustain, soft, sostenuto).
<p>
For other instruments and voice,
notes have additional properties such as attack and timbre,
and these properties may change during the note.
The ideas presented here do not encompass these additional factors,
but could possibly be extended to do so.
<p>
Nuance has a central role in classical music,
as evidenced by the fact that standard repertoire works
are performed and recorded thousands of time,
with the primary difference being the performance nuance.
<p>
Some scores have nuance indications:
tempo markings, slurs, crescendo marks, fermatas, pedal markings, etc.
These do not completely describe the nuance in a human rendition, because:
<p>
<li> The indications are imprecise:
e.g. a fermata mark doesn't say how long the sound lasts,
or how much silence follows.
<li> The indications are ambiguous:
the meaning of marks such as slurs, wedges, and staccato dots
has changed over time, and varies between composers.
Malcolm Bilson \"Knowing the score\"
<li> The indications are incomplete:
they describe the broad strokes of the composer's intended nuance,
but not the details.
Indeed, western music notation is unable to express
basic aspects of nuance such the relative dynamics of notes in a chord.
<p>
In a typical human performance, nuance is guided by score indications
but has other factors:

<li> the expressive intent of the performer;
<li> stylistic conventions, as understood by the performer;
<li> the performer's technique and physical limitations.
These can convey the difficulty of hard sections,
and thus have an expressive role.

<p>
The work described in this paper began with the goal
of developing a computer-based framework for describing performance nuance.
We called this Nuance Description Framework (NDF).
NDF has precisely-defined semantics,
and can describe typical human nuance in a compact way;
for example, idioms like crescendos are described in a single primitive
rather than by per-note deviations.
<p>
NDF has several key features.
<li>
It can express both continuous and discrete nuance components.
'Continuous' means things like crescendos and accelerandos;
'discrete' means momentary things like accents and pauses.
Time-varying components are expressed in data structures
called 'piecewise functions of time'.
<li>
It provides a powerful way of selecting subsets of notes,
based either on explicit 'tags',
or on note attributes such as chord or metric position.
A 'note selector' is a boolean-valued function of these.
<li>
It allows nuance to be factored;
an NDF nuance description is a list of 'transformations',
each of which includes an operation, a PFT, and a note selector.
Each transformation, when applied to a score,
modifies parameters of some or all of the notes.
<p>
NDF is based on an abstract model
with two classes: 'Score', which represents that
based parts of a musical work,
and 'nuance description'.
NDF defines the structure of these classes,
and the semantics of applying a nuance description to a score.
It doesn't specify how they are implemented;
for example, a score could be a MusicXML or MIDI file,
or a Music21 object hierarchy.
<p>
We have developed a 'reference implementation' of NDF in Python
(Section X),
and we describe NDF in terms of Python data structures and APIs.
However it could be implemented using other languages
or data representations, such as JSON.
<p>
NDF has two broad areas of use.
In the first, a human musician develops a nuance description
for a given score,
using an editing system of some sort.
We call this 'nuance specification'.
This could be used, for example, to create a 'virtual performance' of a work.
We discuss this and other applications in Section X.
<p>
The second area, called 'nuance inference',
involves taking a score for a work
and a human performance of the work,
and finding (algorithmically and/or manually)
a nuance description that maps the score to
a rendition that closely approximates the human performance.
This is discussed in Section X.
<p>
The remainder of the paper expands on the above topics.
Section X discusses related work,
and Section X discusses future work and conclusions.

<a name=model></a>
<h2>2. The $mns model</h2>
<h3>2.1 Time</h3>
<p>
$mns uses two notions of time:
<ul>
<li> 'Score time': time as notated in a score,
represented as floating-point numbers.
The scale is arbitrary,
but our convention is that the unit is a 4-beat measure.
Thus, 0.25 (1/4) is a quarter note, and so on.
<li> 'Performance time': a transformed version of score time.
In the final result of applying an $mns description to a score,
performance time is real time, measured in seconds.
</ul>

<p>

<h3>2.2 $score</h3>
<p>
The class $score represents the basic parts of a musical work:
note pitches and notated timings, and measure boundaries if present.
A $score could correspond to a MusicXML file,
a Music21 object hierarchy, or a MIDI file.
These embodiments may contain additional information &mdash;
slurs, dynamic markings, note stem directions, etc. &mdash;
that are not included in the $score.

<p>
The class $note represents a note.
The attributes of a $note N include:
<ul>
<li> Its start time <code>N.time</code> and duration
<code>N.dur</code> in units of score time.
<li> Its pitch (represented, for example, as a MIDI pitch number).
<li> A set of 'tags' (character strings).
For example, `rh` and `lh` could be used to tag
notes in the right and left hand parts.
In a fugue, tags could indicate that a note is part of the fugue theme,
or a particular instance of the theme.
Grace notes could be tagged, and so on.
</ul>
<p>
Some attributes of a $note N are implicit,
based on its context in the score:
<p>
<ul>
<li> Tags `top` or `bottom` are added if N
has the highest or lowest pitch of notes starting at the same time.
<li> `n.nchord` is the number of notes
with the same start time as `n`, and `n.nchord_pos` is `n`'s pitch order in this set
(0 = lowest, 1 = 2nd lowest, etc.).
</ul>

<p>
The class $measure represents a measure.
Each measure is described by its start time and duration,
which are score times.
Measures must be non-overlapping.
A Measure can also have a 'type' tag,
typically a string representing the measure's
duration and metric structure (e.g. '2+2+3/8').
<p>
If a note N lies within a measure, it has two additional attributes:
<ul>
<li>
N.measure_offset: the time offset from the last measure start.
<li>
N.measure_type: the type of the measure.
</ul>
<p>
If a note lies on the boundary between two measures,
it's considered to be in the second one.

<h3>2.3 Note selectors</h3>
<p>
A 'note selector' is a Boolean-valued expression involving the attributes
of a note N.
Note selectors
identify sets of notes within a $score.
We use Python syntax for these expressions.
For example, the expression
<p>
<pre>
'rh' in n.tags and n.dur == 1/2
</pre>
<p>
selects all half notes in the right hand.
We could select notes in a particular range of score time,
at a particular measure offset, and so on.
<p>
In Python, the type of note selectors is
<pre>
type Selector = Callable[[$note], bool] | None
</pre>
<p>

<h3>2.4 Piecewise functions of time</h3>
<p>
There are two general types of transformations:
<ul>
<li> Continuous:
a smooth (or piecewise smooth) change in tempo, volume,
or other parameter.
For keyboard music they affect only note starts and ends,
but conceptually they are continuous.
The same transformation could be applied to
whole notes or 64ths.

<li> Discrete:
pauses (tempo) and accents (volume).
These occur at specific points in time.
They may occur in repeating time patterns,
at irregular times, or at single times.
</ul>
<p>
Many components of nuance involve quantities
(like tempo and volume) that change over time.
In $mns, these are typically described as functions of score time.
These functions are specified as a sequence of 'primitives',
A function defined in this way is called a 'piecewise function of time' (PFT).
<p>
In Python, PFT primitives are represented by objects
derived from a base class PFTPrimitive.
There are two kinds of PFT primitives:
<p>
'Interval primitives' 
describe a function over a time interval [0, dt] where dt>0.
Examples:
<pre>
class Linear(PFTPrimitive)      # a linear function
class ExpCurve(PFTPrimitive)    # an exponential function
</pre>
Primitives could be defined for other functions
(polynomial, spline, etc.).

<p>
'Momentary primitives'
represent a value at a single moment;
they have zero duration.
Examples:
<pre>
class Accent(PFTPrimitive)      # an accent (volume)
class Pause(PFTPrimitive)       # a pause (timing)
class Shift(PFTPrimitive)       # a time shift (timing)
</pre>
<p>
PFTs are represented by lists of PFT primitives:
<pre>
type PFT = list[PFTPrimitive]
</pre>

<p>
For example:
<pre>
[
    Linear(25, 15, 2/1, closed_start = True)
    Linear(15, 20, 1/1, closed_end = True)
    Linear(10, 15, 2/1, closed_start = False)
]
</pre>
<p>
defines a function that varies linearly
from 25 to 15 over 2 4-beat measures,
from 15 to 20 over 1 measure,
then from 10 to 15 over 2 measures.
<p>
<center>
<img src=pft.png width=500>
<br>
Figure 2: A piecewise function of time is a concatenation of primitives.
</center>
<p>
Depending on their use, interval primitives objects
may define various members functions:
<p>
<pre>
closed_start(): bool
closed_end(): bool
</pre>
Whether the primitive defines a value at its start and end times.
<pre>
value(t: float): float
</pre>
the value of F at time t (0<=t<=dt).
<pre>
integral(t: float): float
</pre>
the integral of F from 0 to t (0<=t<=dt)
<pre>
integral_reciprocal(t: float): float
</pre>
the integral of the reciprocal of F from 0 to t.
<p>
$mns uses PFTs for several purposes.
When a PFT is used to describe tempo (see below)
its integrals are used, not its values,
and closure at endpoints is not relevant.
When a PFT is used to describe volume,
the value is used, and closure matters.
<p>
<h3>2.2.1 Linear PFT primitive</h3>
<p>
The PFT primitive representing a linear function is:
<p>
<pre>
Linear(
    y0: float,
    y1: float,
    dt: float,
    closed_start: bool = True,
    closed_end: bool = False
)
</pre>
<p>
This represents a linear function F with F(0)=y0 and F(dt)=y1.
Its integral is
<p>
t+(t^2)/2
</p>
and the integral of its reciprocal is
<p>
(1/a)ln(at+b)
<p>
where a is the slope (y1-y0)/dt.

<p>
<h3>2.2.2 Exponential PFT primitive</h3>
<p>
Another PFT primitive represents an exponential function of the form
<p>
F(t) = y0 + y1*(1-C^(t/dt))/(1-C)
</p>
where C is a 'curvature' parameter.
This function varies from y0 to y1 over 0..dt.
When C is positive, F is concave up,
so the change is concentrated in the latter part of the interval.
When C is zero, F is linear.
Examples are shown in Figure 3.
<p>
<center>
<table>
<tr>
<td><img src=exp2.png width=300></td>
<td><img src=exp5.png width=300></td>
</tr><tr>
<td><img src=exp-2.png width=300></td>
<td><img src=exp-5.png width=300></td>
</tr>
</table>
<p>
Figure 3: Exponential primitives with different curvatures.
</center>

<p>
The integral of F is
<p>
<p>
and the integral of the reciprocal is
<p>
(ln(c^x) - ln(1+c^x))/ln(c)
<p>

<h3>2.2.3 Momentary PFT primitives</h3>
<p>
MNS defines
several momentary primitives, used for different purposes.
<pre>
Accent(value: float)
</pre>

Represents a volume adjustment for notes starting
at a particular time (see Section X).
The surrounding interval segments must be open
at their respective ends.

<pre>
Pause(value: float, after: bool)
</pre>
Represents a pause of the given performance time duration.
The pause shifts the start times of all subsequent events.
If <code>after</code> is True, the pause occurs
after the events at the current time;
otherwise it occurs before them.
There can be pauses of both types (before and after)
at a particular time.

<pre>
Shift(value: float)
</pre>
Represents a shift in the performance times of events
at the current time.
This can be used for 'agogic accents',
in which melody notes are brought out by
shifting them slightly after accompaniment notes.
Unlike Pause, subsequent events are not affected.

<h3>2.3  Transformations</h3>
<p>
A MNS specification consists of a sequence of 'transformations'.
<p>
Each transformation consists of

<p>
<li> An 'operator' indicating the type of the transformation.
The set of operators is listed in the following section.
<li> A PFT
<li> A note selector

<p>
A transformation acts on a $score, modifying it in some way.
In the following, we notate transformations
as member functions of the $score class,
there the name of the function is the operator.
<p>

<a name=timing></a>

<h2>3. Timing</h2>
<p>
$mns supports three kinds of timing adjustment.
<p>
<b>Tempo control</b>: the performance times of note starts and
ends are changed according to a 'tempo function',
which is integrated on the intervals between events.
The tempo function can include pauses before and/or
after particular score times.
Tempo functions are represented as PFTs.
<p>
<b>Time shifting</b>.
Notes can be shifted &mdash; moved earlier or later &mdash;
in performance time.
Generally the duration is changed so that the end time of the note
remains fixed.
Other notes are not changed
(unlike pauses, which postpone all subsequent notes).
$mns defines several time-shift transformations:
for example,
'rolling' a chord with specified shifts for each chord note,
or using a PFT to specify varying 'agogic accents'
in which melody notes are played slightly after accompaniment notes.
<p>
<b>Articulation control:</b> Note durations
(in either score time or performance time)
can be scaled or set to particular values,
to express legato, portamento, and staccato.
You can do this in various ways,
including continuous variation of articulation using a PFT.

<p>
These adjustments can be layered.
For example, one could specify several layers of tempo adjustment,
followed by time shifting.
The only constraint is that adjustments to score time must precede adjustments to performance time.

<h3>3.1 Tempo control</h3>
<p>
Tempo variation is described with a PFT.
There are three options for the meaning of the PFT:
<ul>
<li> Slowness (or inverse tempo):
The PFT value is the rate of change of performance time
with respect to score time.
If t0 and t1 are score times,
the PFT scales the interval from t0 to t1
by the integral of the PFT between those points.
<li> Tempo:
the PFT value is the rate of change of score time
with respect to performance time.
The PFT scales time by
the integral of the inverse of the PFT.
<li> Pseudo-tempo:
an approximation to tempo for PFT primitive types where
it's hard to compute the integral of the inverse.
Instead, we invert the tempo parameters of
the PFT primitives, and treat that as a slowness function.
</ul>
<p>
In all cases, a PFT can include momentary primitives.
These represent pauses;
their value is in units of performance time.
They act like Dirac delta functions;
their integral depends on whether the PFT
is tempo or inverse tempo.
<p>
<pre>
Score.tempo_adjust_pft(
    pft: PFT,
    t0: float,
    selector: Selector = None,
    normalize: bool = False,
    bpm: bool =True
)
</pre>
<p>
This adjusts the performance time of the selected notes,
starting at t0,
according to the tempo function specified by the PFT.

<p>
If bpm is False, the value of the tempo function is
the rate of change of performance time with respect to score time.
The performance duration of a score-time interval
is the integral of F over that interval.
We call this an 'inverse tempo function' because
larger values mean slower:
2.0 means go half as fast, 0.5 means go twice as fast.
<p>
If 'bpm' is True,
the value of the tempo function is in beats per minute.
For example, 120 means go twice as fast.
The tempo function represents tempo rather than inverse tempo.
<p>
In either case, the tempo function can also contain
Pause primitives, which represent a pause of a given performance time.
<p>
If 'normalize' is set, the tempo function is scaled
so that its average value is one;
in other words, its start and end points remain fixed,
but events between them can move.
This can be used, for example, to apply rubato
a particular voice over a given period,
and have it synch up with other voices at the end of that period.
<p>
Example: Chopin
<p>
The semantics of tempo_adjust_pft() are as follows (see Figure X):
<p>
<ul>
<li> Make a list of all 'events' (note start/end, pedal start/end)
ordered by score time.
Each event has a score time and a performance time.
<li> Scan this list, processing events that satisfy the note selector
(if given) and that lie within the domain of the PFT.
<li> For each pair of consecutive events E1 and E2,
compute the average A of the PFT between the score times of E1 and E2
(i.e. the integral of the PFT over this interval divided by the interval size).
<li>
Let dt be the difference in original performance time between E1 and E2.
Change the performance time of E2 to be
the (updated) performance time of E1 plus A*dt.
<li>
What about pauses?
before: Earlier notes that end at or after t are elongated.
after: Notes that start at t are elongated.
</ul>
<center>
<img src=timing.png width=600>
<br>
<b>Figure 1: The semantics of tempo control PFTs.</b>
</center>
<p>

<h3>3.2 Time shifts</h3>
<p>
In the following: when change start perf time,
adjust perf duration to keep same end time.
<pre>
Score.time_shift_pft(
    pft: PFT,
    t0: float = 0,
    selector: Selector = None
)
</pre>
For notes N that satisfy the selector
and for which t0 < N.time <= t0+pft.duration(),
add pft.value(N.time - t0) to N.perf_time.
This can be used to give agogic accents to notes at particular times,
or to shift notes by continuously-varying amounts.
<pre>
Score.roll(
    t: float,
    offsets: list[float],
    is_up: bool = True,
    selector: Selector = None
)
</pre>
<p>
Roll a chord.
'offsets' is a list of time offsets.
These offsets are added to the performance start times of notes
that start at score time t.
If 'is_up' is true, they are applied from bottom pitch upwards;
otherwise from top pitch downward.
<p>
<pre>
Score.t_adjust_list(
    offsets: list[float],
    selector: Selector
)
</pre>
<p>
'offsets' is a list of time offsets (seconds).
They are added to the start times of notes satisfying the selector,
in time order.
Example?
<p>
<pre>
Score.t_adjust_notes(
    offset: float,
    selector: Selector
)
</pre>
<p>
The given time offset (seconds) is added to the start times of
all notes satisfying the selector.
<p>
<pre>
Score.t_adjust_func(
    func: NotetoFloat,
    selector: Selector
):
</pre>
<p>
For each note satisfying the selector,
the given function is called with that note,
and the result is added to the note's start time.
Example?
<p>

<h3>3.3. Articulation</h3>
<p>
<pre>
Score.perf_dur_rel(
    factor: float,
    selector: Selector = None
)
</pre>
<p>
Multiply the duration of the selected notes by the given factor.
<p>
<pre>
Score.perf_dur_abs(
    t: float,
    selector: Selector = None
)
</pre>
<p>
Set the performance duration of the selected notes to the given value
(performance time).
<p>
<pre>
Score.perf_dur_func(
    f: NotetoFloat,
    selector: Selector = None
)
</pre>
<p>
Set the performance duration of selected notes N to the value f(N).

<h3>3.4 Layering timing adjustment</h3>
<p>
PFT-based timing adjustments without pauses commute,
and the order in which they're applied doesn't matter.
Other adjustments general don't commute.
A typical order of adjustments (see Section X):
<ul>
<li> Tempo (non-pause) PFTs
<li> Pause PFTs
<li> Shift PFTs
</ul>

<a name=pedal></a>
<h2>4. Pedal control</h2>
<h3>4.1 Standard pedals</h3>
<p>
Grand pianos have three pedals:
<ul>
<li> Sustain pedal: when fully depressed, the dampers are lifted so that
a) notes continue to sound after their key is released;
b) all strings vibrate sympathetically.
If the pedal is gradually raised, the dampers are gradually lowered;
this 'half-pedaling' (or more accurately, fractional pedaling)
can be used to create various effects.
<li> Sostenuto pedal: like the sustain pedal,
but when it is depressed only the dampers
of currently depressed keys remain lifted.
Half-pedaling works similarly to the sustain pedal.
<li> Soft pedal: the hammers are shifted so that they
hit only 2 of the 3 strings of treble notes.
This reduces loudness and typically softens the timbre.
Fractional pedaling can also be used; its effects vary between pianos.
</ul>
<p>
Some MIDI synthesizers, such as PianoTeq,
implement all three pedal types,
and implement fractional pedaling for each one.
<p>
Like other aspects of nuance,
pedaling is critical to the sound of a performance,
but few scores notate it at all,
much less completely and precisely.
<p>
$mns provides a mechanism for specifying pedal use.
The level of a particular pedal can be specified as a PFT
consisting of Linear primitives with value in [0,1],
where 1 means the pedal is fully depressed
and 0 means it's lifted.
<p>
When a pedal change is simultaneous with notes,
we need to be able to specify
whether the change occurs before or after the notes are played.
For sustain and sostenuto pedals,
we also need to be able to specify momentary lifting of the pedal.
We handle these requirements using the closure attributes
of PFT primitives.
Suppose that P1 and P2 are consecutive primitives;
P1 ends and P2 begins at time t,
and one or more notes start at t.
The semantics of the PFT depend on the closure of P1 and P2 as follows:
<p>
<table border=1 cellpadding=4>
<tr><td>end of P1</td><td>start of P2</td><td>Semantics</td></tr>
<tr><td>open</td><td>open</td><td>lift pedal, play notes, pedal X</td></tr>
<tr><td>open</td><td>closed</td><td>lift pedal, pedal X, play notes</td></tr>
<tr><td>closed</td><td>open</td><td>play notes</td></tr>
<tr><td>closed</td><td>closed</td><td>play notes, pedal X</td></tr>
<table>

<p>
The Linear primitive allows expression of
continuously-changing fractional pedal.
For example,
<code>
Linear(1/1, 1.0, .5)
</code>
represents at pedal change from fully depressed to half depressed
over 4 beats.
In the case of MIDI output this produces
a sequence of continuous-controller commands
with values ranging from 127 to 64.

<p>
To apply pedal PFT to a $score starting at time t0:
<pre>
Score.pedal_pft(
    pft: PFT,
    type: PedalType,
    t0: float
)
</pre>

<h3>4.2 Virtual sustain pedals</h3>
<p>
Sometimes it's useful to sustain only certain keys.
The sustain pedal can't do this: it affects all keys.
The sostenuto pedal affects a subset of keys,
but its semantics limit its use to a fairly small set of situations.
$mns has a mechanism called 'virtual sustain pedal'
that is like a sustain pedal that applies to only a specific subset of notes.

<p>
The use of a virtual sustain pedal
is specified by the same type of PFT as for standard pedals;
the only allowed values are 0 (pedal off) or 1 (pedal on).
Such a PFT is applied to a score with
<pre>
Score.vsustain_pft(
    pft: PFT,
    t0: float,
    selector: Selector
)
</pre>
The semantics are: if a note N is selected,
and the pedal is on at its start (score) time,
N.dur is adjusted so that N is sustained at least until the
pedal is released.
<p>
Virtual sustain pedals can be used,
for example, to sustain an accompaniment figure
without affecting the melody.
<p>
Example?
<p>
Compared to standard sustain pedals,
virtual sustain pedals are more flexible
in terms of what keys are sustained.
They lack two features of standard pedals: there is no fractional pedal,
and, acoustically, there is no sympathetic resonance of open strings.

<h3>4.3 Implementation and layering of pedal specifications</h3>
<p>
Pedal specifications must precede timing adjustments.
Timing adjustments (including time shifts)
must affect pedal usage as well as notes.
For virtual pedals this happens automatically.
For standard pedals, if a note at time T is shifted backward,
pedals active at T are shifted backward by the same amount.

<p>
Uses of the standard pedals can't be layered;
that is, two PFTs controlling a particular pedal can't overlap in time.
However, virtual sustain PFTs can overlap standard pedal PFTs.

<a name=dynamics></a>
<h2>5. Dynamics</h2>
<p>
In $mns, the volume of a note is represented by floating point 0..1
(soft to loud).
This may be mapped to a MIDI velocity (0..127),
in which case the actual loudness depends on the synthesis engine.
Notes initially have volume 0.5.
<p>
There are three 'modes' of volume adjustment.
In each case there is an adjustment factor X,
which may vary continuously over time.

<ul>
<li> VOL_MULT: the note volume is multiplied by X,
which typically is in [0, 2].
This maps the default volume 0.5 to the full range [0, 1].
Adjustments that use this mode can be applied in any order.

<li> VOL_ADD: X is added to the note volume.
X is typically around .1 or .2.
This is useful for bringing out melody notes when the overall volume is low.

<li> VOL_SET: the note volume is set to X/2. X is in [0, 2].
(The division by 2 means that the scale is the same as as for VOL_MULT).

</ul>

Multiple adjustments can result in levels > 1; if this happens, a warning message is shown and the volume is set to 1.

<p>
The transformation
<p>
<pre>
Score.vol_adjust_pft(
    pft: PFT,
    t0: float = 0,
    selector = None
)
</pre>
<p>
adjusts the volume of a set of notes according to a function of time.
'pft' is a PFT,
and 'selector' is a note selector
The volume of a selected note N
in the domain of the PFT is adjusted by the factor pft(t),
where t is N.time - t<sub>0</sub>.
<p>
This can be used to set the overall volume of the piece.
It can be used to shape the dynamics of an inner voice by selecting
the tag used for that voice.
<p>
Other transformations adjust volume explicitly
(not necessarily as a function of time).
<pre>
Score.vol_adjust(
    factor: float,
    selector: Selector = None
)
Score.vol_adjust(
    func: NoteToFloat,
    selector: Selector = None
)
</pre>
These adjust the volumes of the selected notes.
If the 1st argument is a function,
its argument is a note and it returns an adjustment factor.
Otherwise the 1st argument is an adjustment factor.
For example,
<p>
<pre>
score.vol_adjust(lambda n: random.normal()*.01)
</pre>
<p>
makes a small normally-distributed adjustment to the volume of all notes.

<p>
<pre>
score.vol_adjust(ns, .9, lambda n: n.measure_offset == 2)
score.vol_adjust(ns, .8, lambda n: n.measure_offset in [1,3])
score.vol_adjust(ns, .7, lambda n: n.measure_offset not in [0,1,2,3])
</pre>
<p>
emphasizes the strong beats of 4/4 measures.

<h3>5.1 Layering volume transformations</h3>
<p>
Volume adjustments are typically layered (see Section X).
Multiplicative adjustments commute,
so the order in which they're applied doesn't matter.
Other adjustments generally do not commute.
Typically the order is:
<ul>
<li> one or more multiplicative adjustments
<li> adjustments that increment volume
<li> adjustments set volume to specific values
</ul>

<h2>7. Specifying nuance</h2>
<p>
In many applications of NDF, a human musician
(composer or performer) manually creates a nuance description for a work.
We call this 'nuance specification'.
<p>
We developed nuance specifications to produce
renditions of piano pieces in a variety of styles,
with the goal of approximating human performances.
In this section we describe some principles that we found useful.
But nuance specification - like practicing for a physical performance -
is a personal process.

<h3>7.1 Nuance structure</h3>
<p>
The first step in developing a nuance specification
is to decide on a structure:
a set of layers, each of which has a particular purpose.
The goal is that when one wants to change something,
it's clear which layer is involved.
<p>
We found that, to do this, we ended up using,
for both timing and dynamics:

<li> One or more layers of continous transformation:
typically a layer at the phrase level (1-8 measures or so)
and a layer at shorter time scale (1-4 beats).
<li> A layer of repeating discrete change
(for example, patterns of accents on the beats within a measure,
or pauses within a measure).
<li> A layer of irregular discrete change
(for example, pauses at phrase ends,
or agogic accents on particular melody notes).
</ul>

<p>
Typically one can use separate controls for the different parts:
for example, the left and right hand parts,
and the melodies.
This can be done by tagging these parts and using not selectors.
<p>
Pedal control:
<ul>
<li> A PFT for the standard sustain pedal.
<li> PFTs for virtual sustain pedals affecting only some voices
(e.g. left or right hand).
</ul>

<h3>7.2 The process of developing a nuance specification</h3>
<p>
Developing a nuanced performance of a work with $mns is
analogous to practicing on a physical instrument.
One starts by developing a mental model of the piece
and a 'rough draft' of a nuance specification.
This is followed by an iterative process of
<ul>
<li> Listening to (part of) the rendition
<li> Identifying a deviation from the mental model
<li> Identifying and changing the relevant part of the
nuance specification.
</ul>
<p>
<ul>
<li> Work on a short part of the piece (say, one measure or phrase).
<li> Work on one voice at a time.
<li> Work on one layer at a time.
</ul>

<h3>8. Nuance scripting</h3>
<p>
A long and complex piece typically has repetition at multiple levels.
The nuance for such a piece will typically also have structure.
It's convenient to be able to express:


<li> Repetition.
you might want to define a dynamic pattern
and apply it 16 times in a row,
rather than defining it 16 times.

<li> Parameterization:
transformations parameters can be variables,
and changing the value of a variable affects
all the transformations that use it.
E.g., to emphasize the strong beats in each measure,
one can define a pattern of emphases,
and then apply it to multiple measures.

<li> Functions.
More generally

<p>
These are features of every programming language,
so we can achieve these capabilities by
wrapping NDF in a programming language:
i.e., providing an API for describing and layering transformations.
We call this 'nuance scripting'.
We have done this in Numula, using Python (see Section X).
Other languages could be used as well.
<p>
Graphical interfaces for editing nuance
could potentially be enhanced to provide scripting-like capabilities.
We believe that these are necessary for non-trivial applications.

<a name=editing></a>
<h2>8. Editing interfaces</h2>
<p>
What kind of UI (user interface) would facilitate creating
and editing nuance specifications &mdash;
in particular, for transcribing one's mental model of a performance?
<p>
This generally involves changing every parameter &mdash;
start time, duration, volume &mdash; of every note.
We can imagine a GUI that shows a piano-roll representation
of the score and lets one click on notes to change their parameters.
This low-level approach would be impossibly tedious.
<p>
Desirable properties of a UI for editing nuance:
<ul>
<li> It can describe nuance at a high level:
an accelerando from 80 to 120 from measures 8 to 13
can be expressed directly rather than by adjusting individual notes.

<li>
One can make an adjustment and hear the effect
quickly and with a minumum of keystrokes and mouse clicks.
</ul>
<p>
Some general approaches:
<ul>
<li> Integrate nuance editing with score editing
(e.g. in Musescore).
Nuance layers would be displayed as 'tracks' below the score.
You can use the mouse to drag and drop nuance primitives,
adjust their parameters, and hear the results.
I think this might be the best approach.

<p>
It would also be possible to use the nuance to modify
the way the score is displayed:
e.g. to use color or note-head size to express dynamics.

<li> Express nuance in a programming language.
This has been done in
<a href=https://github.com/davidpanderson/music/wiki>Numula</a>,
a Python-based system for virtual performance and algorithmic composition.
</ul>
<p>
In its original form, the nuance editing cycle in Numula was cumbersome;
each adjustment required locating and editing a value in the source code,
then re-running the program.
To address this, Numula provides a feature called 'Interactive Parameter Adjustment' (IPA)
that streamlines the editing cycle,
reducing it to two keystrokes.

<ul>
<li> a number to select a variable
<li> up or own arrow keys to increment or decrement the variable value
<li> space to play the selected part of the score.
</ul>

<h3> 9.2 Examples</h3>
<p>
We have used Numula to create nuanced performances
of piano pieces in a variety of styles,
ranging from Beethoven to Berio.
Our goal was to create performances that approximated
performances by a skilled human.
<p>
Appassionata

<a name=applications></a>
<h2>9. Applications of nuance specification</h2>
<p>
Let's assume that we have a formalism describing nuance,
and that we have software tools
that make it easy to create and edit 'nuance specifications' for pieces.
These capabilities would have several applications:
<p>
<h3>9.1 Composition</h3>
<p>
As a composer writes a piece,
using a score editor such as MuseScore or Sibelius,
they could also develop a nuance specification for the piece.
The audio rendering function of the score editor could
use this to produce nuanced renditions of the piece.
This would facilitate the composition process
and would convey the composer's musical intentions
to prospective performers.
<p>
$mns could be used in a variety of musical contexts.
For example, it could be used in combination
with a graphical score editor to generate a MIDI-based
rendition of a composition; see Figure 1.

<center>
<img src=flow.gif width=400>
<br>
Figure 1: $mns as part of a system for composition.
</center>
<p>

<h3>9.2 Virtual performance</h3>
<p>
Performers could create nuanced
<a href=prep_perf.php>virtual performances</a> of pieces,
in which they render the piece using a computer
rather than a traditional instrument.

<h3>9.3 Performance pedagogy</h3>
<p>
A piano teacher's instruction to a student could be represented
as a nuance specification which guides the student's practice.
Feedback could be given in various ways.
For example, as a student practices a piece they could see a
'virtual conductor' that shows, on a screen,
a representation of the target nuance.
Or a 'virtual coach' could make suggestions
(musical and/or technical) to the student based on
the differences between their playing and the nuance specification.

<h3>9.4 Ensemble rehearsal and practice</h3>
<p>
When an ensemble (say, a piano duo) rehearses together,
they could record their interpretive decisions as a nuance specification.
They could then use this to guide their individual practice
(perhaps using a 'virtual conductor' as described above).

<h3>9.5 Sharing and archival</h3>
<p>
Web sites like
<a href=https://imslp.org/>IMSLP</a> let people
share scores for musical works.
Such sites could also include user-supplied nuance descriptions
for these works.
This would provide a framework for sharing and discussing interpretations.
<a name=mns></a>
<p>

<a name=numula></a>
<h2>6. Numula</h2>
<p>
Numula is a Python library that implements $mns.
It implements the classes listed above:
Score, Note, PFT, etc.
Its Score class implements the transformation functions.
<p>
Numula is a stand-alone system for creating nuanced music
completely in Python.

MIDI file -> Score object

JSON file -> PFT
<p>
Shorthand notations
<p>
Numula provides a number of textual shorthands
for expressing both scores and nuance.
For example
tempo
volume
pedal
<p>
These are compiled into PFTs (or Scores)
picture:
score shorthand -> Score object
nuance shorthand -> PFTs
                -> $mns engine
                -> MIDI
<p>
common features:
nested looping
params (f strings)
measure checking

<p>
IPA

<h2>10. Nuance inference</h2>
<p>
How can we infer the nuance from a human performance?
More precisely:
given a score and a performance of the score
(as a sound file or MIDI file)
how can we find an MNS nuance spec S
which, when applied to the score, generates the performance?
<p>
This problem is ill-posed: there are infinitely many specs
that exactly reproduce the performance
by specifying the time and volume of each note.
This type of spec is not what we're looking for.
<p>
So we need to refine the problem.
For some notion of the 'complexity' of a spec,
what is the least complex that generates
the performance within some tolerance?
<p>
Even this is a bit ill-posed;
the solution is not necessarily unique.
For example, a crescendo and a sequence of accents
might have the same effect,
or a ritardando and a sequence of pauses.

<h3>10.1 Terminology</h3>
<p>
Tagged score
<p>
Constrained nuance structure
<p>
Complexity of a spec
<p>
Score rendition, error

<h3>10.1 How to infer nuance</h3>
<p>
Volume:
inuitively, we want to work from long to short:
to identify phrase-level features, then measure-level, then single notes.
So we might start by:
<p>
<li> Identify a segment of the performance where the volume trends up or down.
<li> Find the primitive type (linear, exponential, etc.)
that best fits the volume contour,
and find the best-fit (e.g. least-squares) parameters
<li> Continue, finding more such disjoint segments,
and assembling the resulting primitives into a PFT.
<p>
We can then subtract this volume adjustment from the performance,
leaving a residue.
We then fit shorter (beat- or measure-level) primitives in a similar way.
<p>
From the resulting residue, we fit accents or patterns of accents.
<p>
We can analyze timing in a similar way:
fitting long tempo primitives,
then shorter primitives, then pauses.
<p>
The above processes might be automated, or manual,
or a hybrid of the two.
We might manually find the endpoints of a crescendo,
then let the computer choose the best primitive and parameters.
<p>
We might need to change the nuance structure along the way.

<p>
<h3>10.2 Applications of nuance inference</h3>
<p>
The first step in this process is to extract
nuance from a large set human performances:
<li> Get a corpus of performances as MIDI files,
or audio recordings converted to MIDI by software.
For each performance get a representation of the score,
e.g. as MusicXML or MIDI.
<li> Computationally find the correspondence of notes between
performance and score (there might be mistakes or other noise).

<p>
<h3>10.2.1 Performance style analysis</h3>
<p>
Having a framework for describing nuance would
enable rigorous comparative analysis of performance practice.
<p>
Compare the performance styles of individual performers
playing the same piece.
Infer one to get nuance structure,
then others using same structure.
Find 'nuance signature' of performers.
<p>
We could 
look for stylistic trends from different time periods,
or different countries or conservatories.

<h3>10.2.2 PFT primitive selection</h3>
<p>
The goal in designing the set of primitives
is to find a small 'basis set' of transformations,
each with a small number of parameters,
that can achieve the desired specifications &mdash;
for example, that can closely approximate typical human performances.
<p>
Having the mechanism of nuance inference
also lets us evaluate PFT primitives.
We have discussed linear and (more generally) exponential primitives,
but there are many other possibilities:
polynomial, trigonometric, and logarithmic functions, spline curves, etc.
Nuance inference lets us demonstrate whether each of these
is actually used in human performance.
<p>
It may turn out that the optimal set of primitives depends on
the period of the performance,
the period and style of the composition,
the individual performer, and so on.

<a name=related></a>
<h2>10. Related work</h2>
<p>
There has been some research in this general area.
Some papers study the statistics of deviation from the score,
but not the actual modeling of it.
<p>
$mns is analogous to
<a href=https://en.wikipedia.org/wiki/CSS>Cascading Style Sheets</a>
(CSS),
a system for specifying the appearance of web pages.
The properties of CSS include:
<ul>
<li> A CSS specification is separate from the web page
(which is typically described in HTML and/or Javascript).
When a CSS specification is applied to a web page,
it can change the attributes of the page's elements
(size, color, etc.) and the layout of the page.

<li> CSS files can be \"layered\".
They are applied in a particular order,
and later files can extend or override the effects of earlier files.

<li> CSS specifications can refer to subsets of the HTML elements
using 'selectors' involving the element tags, classes, and IDs.
</ul>

<p>
$mns has similar properties.
<a name=future></a>

<h2>11. Future work</h2>

<h3>11.2 Note selection</h3>
<p>
$mns's selection mechanism is low-level:
notes are tagged based on their pitch position and metric position,
and can be tagged explicitly.
One can imagine higher-level ways of selecting notes,
based on musical semantics:
<ul>
<li> Harmony: tag notes based on their roles
in cadences, their chord position, and so on.
<li> Phrase structure:
add tags that allow the specification of, for example,
a small ritardando at the end of each phrase,
or accents on the high points of phrases.
</ul>
... and so on.
<p>
<h3>11.3 Non-keyboard instruments</h3>
<p>
$mns could be extended to handle scores with multiple instruments.
Note tags could include the instrument type
(e.g. 'violin') and instance (e.g. 'violin 1').
<p>
$mns could be extended to include other note parameters:
<ul>
<li> Attack parameters.
<li> Variation in pitch, timbre or volume during a note.
</ul>

<h3>11.4 PFT primitives</h3>
<p>
<h3>11.5 Uses of AI</h3>
<p>
use of AI for nuance inference
<p>
other uses of AI

<a name=conclusion></a>
<h2>12. Conclusion</h2>
<p>
As music evolves, computers are increasingly important tools for
composition, pedagogy, and performance.
High-resolution nuance specification makes nuance a first-class citizen,
along with scores and sounds.
This will not replace the human component of nuance,
or the spontaneity of live performance;
rather, it will provide tools that can enhance these processes
<p>
Rich Kraft contributed ...

<h2>References</h2>
<p>
Malcolm Bilson.
Video: 'Knowing the Score: Do We Know How to Read Urtext Editions and How Can This Lead to Expressive and Passionate Performance?'
Cornell University Press, Ithaca, 2005.
https://www.youtube.com/watch?v=mVGN_YAX03A
<p>
Cascading Style Sheets: https://en.wikipedia.org/wiki/CSS
<p>
PianoTeq: https://en.wikipedia.org/wiki/Pianoteq
<p>
Numula: https://github.com/davidpanderson/numula/
</div>
";

?>
