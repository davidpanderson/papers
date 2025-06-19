<div style="max-width: 700px; font-family:Trebuchet MS; line-height:1.4" align=justify>
<center>
<h1>Specifying nuance in notated music</h1>

<p>
David P. Anderson
<p>
June 1, 2025
</center>

<h2>Abstract</h2>

We present a model for describing performance nuance
in notated keyboard music.
By 'performance nuance' we mean deviations between score and performance
in timing, dynamics, articulation, and pedaling.
The model allows concise expression of complex and layered nuance,
as is typically present in human performances.
We discuss the possible applications of these nuance specifications,
and interfaces for editing them.

<h2>1. Introduction</h2>
<p>
This paper is concerned with performance nuance in notated music.
We focus on keyboard instruments such as piano.
For other instruments (and voice)
a note has additional properties such as attack and timbre,
and its properties may change during the note.
The ideas presented here do not encompass these additional factors,
but could be extended to do so.
<p>
In the context of keyboard music, nuance has several components:
<p>
<ul>
<li> Timing: tempo variation, rubato, pauses, rolled
or other non-simultaneous chords, etc.
<li> Dynamics: crescendos and diminuendos, accents, voicing, etc.
<li> Articulation: legato, staccato, portamento, etc.
<li> The use of pedal (sustain, soft, sostenuto).
</ul>
<p>
Some scores have indications of nuance:
tempo markings, slurs, crescendo marks, fermatas, pedal markings, etc.
These do not completely describe the nuance in a human rendition, because:
<ul>
<li> The indications are imprecise:
e.g. a fermata mark doesn't say how long the sound lasts,
or how much silence follows.
<li> The indications are ambiguous:
the meaning of marks such as slurs, wedges, and staccato dots
has changed over time, and varies between composers.
Malcolm Bilson "Knowing the score"
<li> The indications are incomplete:
they describe the broad strokes of the composer's intended nuance,
but not the details.
Indeed, western music notation is unable to express
basic aspects of nuance such as chord voicing dynamics
(the relative dynamics of simultaneous notes).
</ul>
<p>
A computer rendition of a work using only the score's nuance indications
typically sounds mechanical.
<p>
Some musical styles have associated conventions for nuance.
Score markings and stylistic conventions are just guidelines.
In the end, nuance is up to the performer.
Nuance may be planned in advance,
it may be spontaneous during a particular performance,
or it may be an unintended consequence of the performer's technique.

<p>
To some performers, nuance is ineffable &mdash;
it's something magical that happens during performances,
and to analyze or formalize it is pointless.
This viewpoint is understandable.
But as music evolves,
and as computers are increasingly important tools for
composition, pedagogy, and performance,
there are reasons to expand our ability to represent and manipulate nuance:
to make it a first-class citizen, along with scores and sounds.
Doing so will not replace the human component of nuance,
or the spontaneity of performance;
rather, it will provide tools that can enhance these processes
and that enable new ways of making music.

<p>
Here we present such a formalism,
called MNS (Musical Nuance Specification).
MNS is analogous to
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

<li> CSS files can be "layered".
They are applied in a particular order,
and later files can extend or override the effects of earlier files.

<li> CSS specifications can refer to subsets of the HTML elements
using 'selectors' involving the element tags, classes, and IDs.
</ul>

<p>
MNS has similar properties.
<p>
MNS is conceptual.
It defines two abstract classes:
<ul>
<li> <b>ScoreObject</b>.
This represents the basic parts of a musical work:
note pitches and notated timings, and measure boundaries if present.
<li> <b>MNS specification</b>.
This represents a set of transformations that
are applied to a ScoreObject to produce a rendition of the work.
</ul>
<p>
MNS defines the structure of MNS specifications,
and the semantics of applying these specifications to ScoreObjects.
It does not dictate how these abstractions are implemented.
A ScoreObject could correspond to a MusicXML file,
a Music21 object hierarchy, or a MIDI file.
These embodiments may contain additional information &mdash;
slurs, dynamic markings, note stem directions, etc. &mdash;
that are not included in the ScoreObject.
An MNS specification could be represented as a JSON or XML document.

<p>
In this paper we describe ScoreObjects and MNS specifications
in terms of Python data structures and functions.
<p>
MNS could be used in a variety of musical contexts.
For example, it could be used in combination
with a graphical score editor to generate a MIDI-based
rendition of a composition; see Figure 1.

<center>
<img src=flow.gif width=400>
<br>
Figure 1: MNS as part of a system for composition.
</center>
<p>
Section X discusses other possible applications of MNS.
<p>
We have implemented an MNS interpreter in a Python library called Numula
(see Section x).
Takes a ScoreObject (a Python data structure)
and and MNS spec (Python code).
<p>
The remainder of this paper is structured as follows:
Section 2 describes the basics of MNS.

<h2>2. The MNS model</h2>
<h3>2.1 Time</h3>
<p>
MNS uses two notions of time:
<ul>
<li> "Score time": time as notated in a score,
represented as floating-point numbers.
The scale is arbitrary,
but our convention is that the unit is a 4-beat measure.
Thus 0.25 (1/4) is a quarter note and so on.
<li> "Performance time": a modified version of score time.
In the final result of applying
an MNS specification, performance time is real time, measured in seconds.
</ul>

<p>

<h3>2.2 ScoreObject</h3>
<p>
<p>
A ScoreObject includes a set of Note objects.
The attributes of a note N include:
<ul>
<li> Its start time <code>N.time</code> and duration N.dur in units of score time.
<li> Its pitch (e.g. a MIDI pitch number).
<li> A set of "tags" (character strings).
For example, `rh` and `lh` could be used to tag
notes in the right and left hand parts.
In a fugue, tags could indicate that a note is part of the fugue theme,
or a particular instance of the theme.
Grace notes could be tagged, and so on.
</ul>
<p>
Some attributes of a note N are implicit,
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
A ScoreObject can include a set of Measure objects.
Each is described by its start time and duration,
which are score times.
Measures must be non-overlapping.
A Measure can also have a "type" tag,
typically a string representing the measure's
duration and structure (e.g. "2+2+3/8").
<p>
If measures are specified,
a note N has two additional attributes:
<ul>
<li>
N.measure_offset: the time offset from the last measure start.
<li>
N.measure_type: the type of the measure.
</ul>

<h3>2.3 Note selectors</h3>
<p>
A "note selector" is a Boolean-valued expression involving the attributes
of a note N.
Note selectors
identify sets of notes within a ScoreObject.
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

<h3>2.4 Piecewise functions of time</h3>
<p>
Many components of nuance involve quantities
(like tempo and volume) that change over time.
In MNS, these are typically described as functions of (score) time.
These functions are specified as a sequence of "primitives",
each of which represents
a parameterized function defined over a time interval with a given duration.
A function defined in this way is called a "piecewise function of time" (PFT).
<p>
For example,
<pre>
[
    linear(25, 15, 2/1, closed_start = True)
    linear(15, 20, 1/1, closed_end = True)
    linear(10, 15, 2/1, closed_start = False)
]
</pre>
<p>
might define a function that varies linearly
from 25 to 15 over 1 4-beat measure,
from 15 to 20 over 1 measure,
then from 10 to 15 over 1 measure.
<p>
<center>
<img src=pft.png width=500>
<br>
Figure 2: A piecewise function of time is a concatenation of primitives.
</center>
<p>
There are two types of PFT primitives:
<ul>
<li> 'Interval primitives' 
describe a function over a time interval [0, dt] where dt>0.
<li> 'Momentary primitives' have zero duration.
There are variants for tempo control (representing pauses)
and volume control (representing accents).
</ul>
<p>
Interval primitives, depending on their use,
may define various members functions:
<p>
<pre>
closed_start(): bool
</pre>
Whether the primitive defines a value at its start time.
<pre>
closed_end(): bool
</pre>
<pre>
value(t): float
</pre>
the value of F at time t (0<=t<=dt).
<pre>
integral(t): float
</pre>
the integral of F from 0 to t (0<=t<=dt)
<pre>
integral_reciprocal(t): float
</pre>
the integral of the reciprocal of F from 0 to t.
<p>
MNS uses PFTs for several purposes.
When a PFT is used to describe tempo (see below)
its integrals are used, not its values,
and closure at endpoints is not relevant.
When a PFT is used to describe volume,
the value is used, and closure matters.
<p>
There are potentially many types of PFT primitives (see Section x).
We describe two such types.
<p>
<h3>2.2.1 Linear PFT primitive</h3>
<p>
The interval primitive for a linear function is:
<p>
<pre>
Linear(
    y0: float,
    y1: float,
    dt: float,
    closed_start=True,
    closed_end=False
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
Another interval primitive represents
an exponential function of the form
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
There are several momentary primitives, used for different purposes.
<pre>
Accent(value: float)
</pre>

Represents a volume change for notes starting
at a particular time (see Section X).
The surrounding interval segments must be open
at the respective end.

<pre>
Pause(value: float, after: bool)
</pre>
Represents a pause of the given performance time duration.
The pause shifts the start times of all subsequent events.
If <code>after</code> is set, the pause occurs
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

<h2>3. Timing</h2>
<p>
MNS supports three classes of timing adjustment.
<ul>
<li>
Tempo control: the performance times of note starts and
ends are changed according to a 'tempo function',
which is integrated on the intervals between events.
The tempo function can include pauses before and/or
after particular score times.
Tempo functions are represented as PFTs.

<li>
Time shifting.
Notes can be shifted &mdash; moved earlier or later &mdash;
in performance time.
Generally the duration is changed so that the end time of the note
remains fixed.
Other notes are not changed
(unlike pauses, which postpone all subsequent notes).
There are various functions for doing this.
For example, you can "roll" a chord with specified shifts for each chord note.
You can specify, using a PFT,
a pattern of shifts for creating "agogic accents"
in which melody notes are played slightly after accompaniment notes.

<li>
Articulation control: Note durations
(in either score time or performance time)
can be scaled or set to particular values,
to express legato, portamento, and staccato.
You can do this in various ways,
including continuous variation of articulation using a PFT.

</ul>
<p>
These adjustments can be layered.
For example, you could use several layers of tempo adjustment,
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
an approximation to tempo for primitive types  where
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
tempo_adjust_pft(pft, selector=None, normalize=False, bpm=True)
</pre>
<p>
This adjusts the performance time of the selected notes
according to a function F specified by pft.

<p>
If bpm is False, the value of F is
the rate of change of performance time with respect to score time.
The performance duration of a score-time interval
is the integral of F over that interval.
We call this an "inverse tempo function" because
larger values mean slower:
2.0 means go half as fast, 0.5 means go twice as fast.
<p>
If "bpm" is True,
the value of F is in beats per minute.
For example, 120 means go twice as fast.
F represents tempo rather than inverse tempo.
<p>
If "normalize" is set, F is scaled so
that its average value is one.
This can be used, for example, to apply rubato
a particular voice over a given period,
and have it synch up with other voices at the end of that period.
<p>
The semantics of this function (see Figure X):
<p>
<ul>
<li> Make a list of all "events" (note start/end, pedal start/end)
ordered by score time.
Each event has a score time and a performance time.
<li> Scan this list, processing events the satisfy the note selector
(if any) and that lie within the domain of the PFT.
<li> For each pair of consecutive events E1 and E2,
compute the average A of the PFT between the score times of E1 and E2
(i.e. the integral of the PFT over this interval divided by the interval size).
<li>
Let dt be the difference in original performance time between E1 and E2.
Change the performance time of E2 to be A*dt seconds after
the (updated) performance time of E1.
</ul>
<center>
<img src=timing.png width=600>
<br>
<b>Figure 1: The semantics of tempo control PFTs.</b>
</center>
<p>
<pre>
pause_before(t, dt)
</pre>
<p>
Add a pause of dt seconds before score time t.
Earlier notes that end at or after t are elongated.
<p>
<pre>
pause_after(t, dt)
</pre>
<p>
Add a pause of dt seconds after score time t.
Notes that start at t are elongated.

<h3>3.2 Time shifts</h3>
<p>
In the following: when change start perf time,
adjust perf duration to keep same end time.
<pre>
time_shift_pft(pft: PFT, t0: float=0, selector: Selector=None)
</pre>
For notes N that satisfy the selector
and for which t0 < N.time <= t0+pft.duration(),
add pft.value(N.time - t0) to N.perf_time.
This can be used to give agogic accents to notes at particular times,
or to shift notes by continuously-varying amounts.
<pre>
roll(t: float, offsets: list[float], is_up=True, selector: Selector=None)
</pre>
<p>
Roll a chord.
"offsets" is a list of time offsets.
These offsets are added to the performance start times of notes
that start at score time t.
If "is_up" is true, they are applied from bottom pitch upwards;
otherwise from top pitch downward.
<p>
<pre>
t_adjust_list(offsets: list[float], selector: Selector)
</pre>
<p>
"offsets" is a list of time offsets (seconds).
They are added to the start times of notes satisfying the selector,
in time order.
<p>
<pre>
t_adjust_notes(offset: float, selector: Selector)
</pre>
<p>
The given time offset (seconds) is added to the start times of
all notes satisfying the selector.
<p>
<pre>
t_adjust_func(func: NotetoFloat, selector: Selector):
</pre>
<p>
For each note satisfying the selector,
the given function is called with that note,
and the result is added to the note's start time.
<p>

<h3>3.3. Articulation</h3>
<p>
<pre>
perf_dur_rel(factor: float, selector: Selector=None)
</pre>
<p>
Multiply the duration of the selected notes by the given factor.
<p>
<pre>
perf_dur_abs(t: float, selector: Selector=None)
</pre>
<p>
Set the duration of the selected notes to the given value (seconds).
<p>
<pre>
perf_dur_func(f: NotetoFloat, selector: Selector=None)
</pre>
<p>
Set the duration of a selected note N to the value f(N).

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
but it is seldom notated at all,
much less in a complete and precise way.
<p>
MNS provides a mechanism for specifying pedal use.
The level of a particular pedal can be specified as a PFT
consisting of Linear primitives with value in [0,1],
where 1 means the pedal is fully depressed
and 0 means it's lifted.
<p>
<ul>
When a pedal change is simultaneous with notes,
we need to be able to specify
whether the change occurs before or after the notes are played.
For sustain and sostenuto pedals,
we also need to be able to specify momentary lifting of the pedal.
We handle these requirements using the closure attributes
of PFT primitives.
Suppose that P1 and P2 are consecutive primitives;
P1 ends and P2 begins at a time t,
and some notes start at t.
The semantics of the PFT
depend on the closures of P1 and P2 as follows:
<table>
<tr><td>end of P1</td><td>start of P2</td></tr>
<tr><td>open</td><td>open</td><td>lift pedal, play notes, pedal X</td></tr>
<tr><td>open</td><td>closed</td><td>lift pedal, pedal X, play notes</td></tr>
<tr><td>closed</td><td>open</td><td>play notes, </td></tr>
<tr><td>closed</td><td>closed</td><td>play notes, pedal X</td></tr>
<table>

<p>
The Linear primitive allows expression of changing
fractional pedal.
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
A pedal PFT can be applied to a ScoreObject using
<pre>
pedal_pft(pft: PFT, type: PedalType, t0: float)
</pre>

<h3>4.2 Virtual sustain pedals</h3>
<p>
Sometimes it's useful to sustain only certain keys.
The sustain pedal can't do this: it affects all keys.
The sostenuto pedal affects a subset of keys,
but its semantics limit its use to a fairly small set of situations.
MNS has a mechanism called "virtual sustain pedal"
that is like a sustain pedal that applies to only a specific subset of notes.

<p>
The use of a virtual sustain pedal
is specified by the same type of PFT as for standard pedals;
the only allowed values are 0 (pedal off) or 1 (pedal on).
Such a PFT is applied to a score with
<pre>
vsustain_pft(pft: PFT, t0: float, selector: Selector)
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
and no sympathetic resonance of open strings.

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

<h2>5. Dynamics</h2>
<p>
In MNS, the volume of a note is represented by floating point 0..1
(soft to loud).
This may be mapped to a MIDI velocity (0..127),
in which case the actual loudness depends on the synthesis engine.
Notes initially have volume 0.5.
<p>
There are three "modes" of volume adjustment.
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
(The division by 2 means that you use the same scale as for VOL_MULT).

</ul>

Multiple adjustments can result in levels > 1; if this happens, a warning message is shown and the volume is set to 1.

<p>
The primitive
<p>
<pre>
vol_adjust_pft(pft: PFT, t0: float=0, selector=None)
</pre>
<p>
adjusts the volume of a set of notes according to a function of time.
"pft" is a PFT,
and "selector" is a note selector
The volume of a selected note N
in the domain of the PFT is adjusted by the factor pft(t),
where t is N.time - t<sub>0</sub>.
<p>
This can be used to set the overall volume of the piece.
It can be used to shape the dynamics of an inner voice by selecting
the tag used for that voice.
<p>
Other primitives adjust volume explicitly
(not necessarily as a function of time).
<pre>
vol_adjust(factor: float, selector=None)
vol_adjust(func, selector=None)
</pre>
These adjust the volumes of the selected notes.
If the 1st argument is a function,
its argument is a note and it returns an adjustment factor.
Otherwise the 1st argument is an adjustment factor.
For example,
<p>
<pre>
vol_adjust(lambda n: random.normal()*.01)
</pre>
<p>
makes a small normally-distributed adjustment to the volume of all notes.

<p>
<pre>
vol_adjust(ns, .9, lambda n: n.measure_offset == 2)
vol_adjust(ns, .8, lambda n: n.measure_offset in [1,3])
vol_adjust(ns, .7, lambda n: n.measure_offset not in [0,1,2,3])
</pre>
<p>
emphasizes the strong beats of 4/4 measures.

<h3>5.1 Layering</h3>
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

<h2>6. Numula</h2>
<p>
Numula has 

picture:
score shorthand -> Score object
nuance shorthand -> PFTs
                -> MNS engine
                -> MIDI

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




<h2>7. Examples</h2>
<p>
Appassionata
<h3>7.1 Nuance structure</h3>
<p>
The first step in developing a nuance specification
is to decide on a structure:
a set of layers, each of which has a particular purpose.
The goal is that when you want to change something,
it's clear which layer is involved.
<p>
For example, the examples listed above use variants
of the following structure:
<p>
Timing control:
<ul>
<li> A PFT for tempo control at the phrase level (8+ measures).
<li> A PFT for tempo control at the measure level.
<li> A PFT that specifies pauses.
</ul>
<p>
Volume control:
<p>
<ul>
<li> PFTs for volume control at the phrase level (8+ measures).
<li> PFTs for volume control at the measure level.
<li> PFTs that specifies accents.
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
Developing a nuanced performance of a work with MNS is
analogous to practicing on a physical instrument.
One starts by developing a mental model of the piece
and a "rough draft" of a nuance specification.
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

<h2>8. Editing interfaces</h2>
<p>
What kind of UI (user interface) would facilitate creating
and editing nuance specifications &mdash;
in particular, for transcribing one's mental model of a performance?
<p>
This generally involves changing every parameter &mdash;
start time, duration, volume &mdash; of every note.
We can imagine a GUI that shows a piano-roll representation
of the score and lets you click on notes to change their parameters.
This low-level approach would let you do whatever you want,
but it would be impossibly tedious.
<p>
Desirable properties of a UI for editing nuance:
<ul>
<li> You can describe nuance at a high level:
if you want an accelerando from 80 to 120 from measures 8 to 13,
you can express this directly rather than adjusting individual notes.

<li> You can express repetition.
E.g., if you want to emphasize the strong beats in each measure,
you can define a pattern of emphases,
and then apply it to multiple measures.
<li> Parameterization
<li>
You can make an adjustment and hear the effect
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
I've done this in
<a href=https://github.com/davidpanderson/music/wiki>Numula</a>,
a Python-based system for virtual performance and algorithmic composition.
It's quite powerful, but the user experience isn't great.
You have think in terms of numbers.
In general the nuance editing cycle
(hearing part of the rendition,
modifying the nuance, hearing it again)
takes lots of clicks.
</ul>
<p>
Numula provides a feature called "Interactive Parameter Adjustment" (IPA)
that streamlines the listen/edit cycle,
reducing it to two keystrokes.

<pre>
var('if_pauses', IPA_LAYER, 'on')
var('if_tbeat', IPA_LAYER, 'on')
...
var('if_soprano', IPA_BOOL, True)
var('if_bass', IPA_BOOL, True)
</pre>
<p>
<pre>
var('dv1', IPA_VOL, .07, ['if_vmeas'])        # volume swells in vmeas
var('dv2', IPA_VOL, .1, ['if_vmeas'])

var('p_start', IPA_DT_SEC, .04, ['if_pauses'])

var('tph_1_1', IPA_TEMPO, 40, ['if_tphrase'])
var('tph_1_2', IPA_TEMPO, 60, ['if_tphrase'])
</pre>

<pre>
vm_4_sm = f'[ 0 3/4 {dv1} 1/4 0'
</pre>

<pre>
if if_pauses != 'off':
    ns.tempo_adjust_pft(pauses)
</pre>

<p>
<pre>
variables:
#   name        value  type        tags        description
1   if_pauses   on     Layer
2   if_tbeat    on     Layer
3   if_tphrase  on     Layer
4   if_accents  on     Layer
5   if_vmeas    on     Layer
6   if_vphrase  on     Layer
7   if_soprano  True   Boolean
8   if_bass     True   Boolean
9*  dv1         0.07   Volume      if_vmeas
10  dv2         0.10   Volume      if_vmeas
11  dv3         0.13   Volume      if_vmeas
12  rha1        0.14   Volume      if_accents
13  rha2        0.10   Volume      if_accents
14  rha3        0.05   Volume      if_accents
...
38  start       0/1    Score time              playback start time
39  dur         1/1    Score time              playback duration
40  show        False  Boolean                 show score on playback

</pre>

Type:
<ul>
<li> a number to select a variable
<li> up or own arrow keys to increment or decrement the variable value
<li> space to play the selected part of the score.
</ul>

<h2>9. Applications of nuance specification</h2>
<p>
Let's assume that we have a formalism describing nuance,
and that we have software tools
that make it easy to create and edit "nuance specifications" for pieces.
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
"virtual conductor" that shows, on a screen,
a representation of the target nuance.
Or a "virtual coach" could make suggestions
(musical and/or technical) to the student based on
the differences between their playing and the nuance specification.

<h3>9.4 Ensemble rehearsal and practice</h3>
<p>
When an ensemble (say, a piano duo) rehearses together,
they could record their interpretive decisions as a nuance specification.
They could then use this to guide their individual practice
(perhaps using a "virtual conductor" as described above).

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

<h2>10. Related work</h2>
<h2>11. Future work</h2>

<h3>11.2 Note selection</h3>
<p>
MNS's selection mechanism is low-level:
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
<h3>7.3 Non-keyboard instruments</h3>
<p>
MNS could be extended to handle scores with multiple instruments.
Note tags could include the instrument type
(e.g. "violin") and instance (e.g. "violin 1").
<p>
MNS could be extended to include other note parameters:
<ul>
<li> Attack parameters.
<li> Variation in pitch, timbre or volume during a note.
</ul>

<h3>11.1 PFT primitives</h3>
<p>
MNS currently defines linear and exponential primitive,
and various delta functions.
Many other primitives are possible:
polynomial, logarithmic, trigonometric, spline functions, and so on.
<p>
The goal in designing the set of primitives
is to find a small "basis set" of transformations,
each with a small number of parameters,
that can achieve the desired specifications &mdash;
for example, that can closely approximate typical human performances.
<p>
MNS, for example, has linear and exponential primitives for tempo change.
This was easy to implement &mdash; but can these approximate
ritardandos and accelerandos in practice?
There may be better choices:
Bezier curves, trig functions, polynomials.
<p>
It would be possible to calculate the nuance in human performances,
and find the primitives that approximate it best.
<p>
The first step in this process is to extract
nuance from a large set human performances:

<ul>
<li> Get a corpus of performances as MIDI files,
or audio recordings converted to MIDI by software.
For each performance get a representation of the score,
e.g. as MusicXML or MIDI.

<li> Computationally find the correspondence of notes between
performance and score (there might be mistakes or other noise).
</ul>
<p>
We can then use software to find a transformation
that maps the score to the performance.
This transformation would typically have multiple levels.
A first level would model large-scale fluctuations.
The second level would take the residue from this,
and fit it, possibly with different types of primitives.
At some point the residue presumably would be noise-like,
and its statistical properties could measured.

<p>
Each level would consist of a set of primitives.
The software would consider various families of primitives:
in the case of continuous fluctuations this might include
linear, polynomial, exponential, logarithmic, etc.
The software would use data-fitting techniques to find an optimal basis set.
<p>
It may turn out that the optimal set of primitives depends on
the performance period,
the period and style of music being played,
the individual performer, and so on.
<p>
There has been some research in this general area.
Some papers study the statistics of deviation from the score,
but not the actual modeling of it.

<h2>12. Conclusions</h2>
<p>
Rich Kraft contributed ...

<h2>References</h2>
<p>
Malcolm Bilson:
Video: "Knowing the Score: Do We Know How to Read Urtext Editions and How Can This Lead to Expressive and Passionate Performance? Ithaca".
Cornell University Press, 2005
<p>
Cascading Style Sheets: https://en.wikipedia.org/wiki/CSS
<p>
PianoTeq: https://en.wikipedia.org/wiki/Pianoteq
</div>
