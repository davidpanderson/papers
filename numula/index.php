<?php

$score = '<code>Score</code>';
$note = '<code>Note</code>';
$measure = '<code>Measure</code>';
$nuance_desc = '<code>NuanceDescription</code>';
$pft_primitive = '<code>PFTPrimitive</code>';
$pause = '<code>Pause</code>';

echo '
<html lang="en" xmlns:m="https://www.w3.org/1998/Math/MathML">

<head>
    <meta charset="utf-8">

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=UnifrakturMaguntia">
    <link rel="stylesheet" href="../mathscribe/jqmath-0.4.3.css">

    <script src="../mathscribe/jquery-1.4.3.min.js"></script>
    <script src="../mathscribe/jqmath-etc-0.4.6.min.js" charset="utf-8"></script>
    <!-- <script>M.MathPlayer = false; M.trustHtml = true;</script> -->
</head>
<body>
';
echo "
<div style=\"max-width: 600px; font-size:14px; font-family:Trebuchet MS; line-height:1.4\" align=justify>
<a href=outline.html>Outline</a>
<center>
<h1>Describing performance nuance</h1>

<p>
David P. Anderson
<p>
June 1, 2025
</center>

<h2>Abstract</h2>

We present General Nuance Model (GNM),
a framework for describing performance nuance
(timing, dynamics, articulation, and pedaling)
in notated keyboard music.
GNM can concisely express nuance that closely approximates
typical human performances.
It is designed for systems in which human musicians
- composers and performers -
hand-craft expressive renditions of musical works.
This capability has applications in composition, virtual performance,
and performance pedagogy.
We describe these applications,
and the challenges in creating and editing
long and complex nuance specifications.
We also discuss the possibility of inferring nuance descriptions
from recorded human performances.

<p>

<h2>1. Introduction</h2>
<p>
This paper is concerned with 'performance nuance' in notated music,
by which we mean the differences between notated music
and a performance or rendition of the piece.
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
notes may have additional properties such as attack and timbre,
and these properties may change during the note.
The ideas presented here do not encompass these additional factors,
but could possibly be extended to do so.
<p>
Nuance has a central role in western classical music,
as evidenced by the fact that standard repertoire works
are performed and recorded thousands of times,
with the primary difference being the performance nuance.
<p>
Some scores have nuance indications:
tempo markings, slurs, crescendo marks, fermatas, pedal markings, etc.
These do not completely describe the nuance in a human rendition, because:
<p>
<li> The indications are imprecise:
e.g. a fermata mark doesn't specify the length of the sound,
or of the silence that follows.
<li> The indications are ambiguous:
the meaning of marks such as slurs, wedges, and staccato dots
has changed over time, and varies between composers.
Malcolm Bilson \"Knowing the score\"
<li> The indications are incomplete:
they describe the broad strokes of the composer's intended nuance,
but not the details.
Indeed, western common music notation cannot express
basic aspects of nuance, such the relative volume of notes in a chord.
<p>
In a typical human performance, nuance is guided by score indications
but also has other factors:
the expressive intent of the performer;
stylistic conventions, as understood by the performer;
and the performer's technique and physical limitations,
which can convey the difficulty of hard sections,
and thus have an expressive role.

<p>
The work described in this paper began with the goal
of developing a computer-based framework for describing performance nuance.
We called this General Nuance Model (GNM).
GNM has precisely-defined semantics,
and can describe typical human nuance in a compact way;
for example, gestures like crescendos are described in a single primitive
rather than by per-note deviations.
<p>
GNM has several key features.
<li>
It can express both continuous (crescendos and accelerandos)
and discrete (accents and pauses)
nuance gestures.
Time-varying values are expressed in data structures
called 'piecewise functions of time'.
<li>
It provides a powerful way of selecting subsets of notes,
based either on explicit 'tags',
or on note attributes such as chord or metric position.
A 'note selector' is a boolean-valued function of these.
<li>
It allows nuance to be factored into multiple layers.
Each layer is represented by a 'transformation',
which includes an operation type (e.g. tempo control),
a PFT, and a note selector.
A transformation, when applied to a score,
modifies parameters of some or all of the selected notes.
<p>
GNM has two broad areas of use.
In the first, a human musician develops a nuance description
for a given score,
using an editing system of some sort.
We call this 'nuance specification'.
This could be used, for example, to create a 'virtual performance' of a work.
We discuss this and other applications in Section X.
<p>
The second area, called 'nuance inference',
involves taking a score for a work
and a set of human performances of the work,
and finding (algorithmically and/or manually)
nuance descriptions that closely approximate the human performances.
This is discussed in Section X.
<p>
The remainder of the paper expands on the above topics.
Section X discusses related work,
and Section X discusses future work and conclusions.

<h2>2. The GNM model</h2>
<p>
GNM is based on an abstract model with two classes:
$score, which represents that basic parts of a musical work,
and $nuance_desc, which represents a nuance description.
GNM defines the structure of these classes,
and the semantics of applying a nuance description to a score.
It doesn't specify how they are implemented;
for example, a $score could be derived from a MusicXML or MIDI file,
or a Music21 object hierarchy.
<p>
We have developed a 'reference implementation' of GNM in Python
(Section X).
For this reason, we describe GNM in terms of Python data structures and APIs.
However, GNM could be implemented using other languages
or data representations (such as JSON).

<h3>2.1 Time</h3>
<p>
GNM uses two notions of time:
<li> 'Score time': time as notated in a score,
represented as floating-point numbers.
The scale is arbitrary,
but our convention is that the unit is a 4-beat measure.
Thus, 0.25 (1/4) is a quarter note, and so on.
<li> 'Adjusted time': a transformed version of score time.
In the final result of applying an GNM description to a score,
adjusted time is real time, measured in seconds.

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
<li> N.tags: A set of textual 'tags'.
Tags are assigned both implicitly and explicitly (see below).
</ul>
<p>
Some attributes of a $note N are implicit,
based on its context in the score:
<p>
<ul>
<li> Tags `top` or `bottom` are added if N
has the highest or lowest pitch of notes starting at the same time.
<li> `N.nchord` is the number of notes
with the same start time as N, and `N.nchord_pos` is N's pitch order in this set
(0 = lowest, 1 = 2nd lowest, etc.).
</ul>

<p>
The class $measure represents a measure.
Each measure is described by its start time and duration,
which are score times.
Measures must be non-overlapping.
Like Notes, Measures can have textual tags.
By convention, these tags
include a string representing the measure's
duration and metric structure.
For example, '2+2+3/8' might represent a 7/8 measure
grouped as 2, 2, and 3 eighths.
<p>
If a note N lies within a measure M, N has two additional attributes:
<ul>
<li>
N.measure_offset: the time offset from the start of M.
<li>
N.measure: a reference to M.
</ul>
<p>
If a note lies on the boundary between two measures,
it's considered to be in the second one.

<h3>2.3 Explicit tags</h3>
<p>
Tags are fundamental to GNM; they provide a way to specify
the set of notes to which a nuance gesture is to be applied.
<p>
Implicit tags (like N.top) are conceptually part of the score.
One can also add 'explicit tags',
which are conceptually part of the nuance description.
For example, `rh` and `lh` could be used to tag
notes in the right and left hand parts.
In a fugue, tags could indicate that a note is part of the fugue theme,
or a particular instance of the theme.
Tags could indicate the harmonic function of notes;
e.g. that a note part of a dominant chord in a cadence,
or that it is the 7th in a major seventh chord.
Tags could used to identify hierarchical
(large, medium, and small-scale) structural components of a work.
<p>
GNM does not specify or restrict how tags are assigned.
It could be done manually by a human nuance creator,
or automatically by the software system in which GNM is embedded.

<h3>2.3 Note selectors</h3>
<p>
A 'note selector' is a Boolean-valued function of a note.
A note selector F identifies a set of notes within a $score,
namely the notes N for which F(N) is True.
<p>
We use Python syntax for these expressions.
For example, the function
<p>
<pre>
   lambda n: 'rh' in n.tags and n.dur == 1/2
</pre>
<p>
selects all half notes in the right hand.
<pre>
    '3/4' in n.measure.tags and n.measure_offset == 2/4
</pre>
selects notes on the 3rd beat of 3/4 measures.
<p>
One could select notes in a range of score time,
a range of pitches, and so on.
<p>
In Python, the type of note selectors is
<pre>
   type Selector = Callable[[$note], bool] | None
</pre>
<p>

<h3>2.4 Piecewise functions of time</h3>
<p>
Nuance gestures typically involve values
(like tempo and volume) that change over time.
In GNM, these are described as functions of score time,
and are specified as a sequence of 'primitives',
each of which describes a function defined
either on a time interval or at a point.
A function defined in this way is called a 'piecewise function of time' (PFT).
<p>
In Python, PFT primitives are represented by objects
of types derived from a base class $pft_primitive.
There are two kinds of PFT primitives:
<p>
'Interval primitives' 
describe a function over a time interval [0, dt] where dt &ge; 0.
Examples:
<pre>
   class Linear(PFTPrimitive)      # a segment of a linear function
   class ExpCurve(PFTPrimitive)    # a segment of an exponential function
</pre>
Primitives could be defined for other types of functions
(polynomial, trigonometric, spline, etc.).

<p>
<i>Momentary primitives</i> represent a value at a single moment.
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
from 25 to 15 over two 4-beat measures,
from 15 to 20 over one measure,
then from 10 to 15 over two measures.
Its value at the start of the 4th measure is 20.
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
These indicate whether the primitive defines a value at its start and end times.
<pre>
   value(t: float): float
</pre>
This is the value of F at time t (0<=t<=dt).
<pre>
   integral(t: float): float
</pre>
This is the integral of F from 0 to t (0<=t<=dt)
<pre>
   integral_reciprocal(t: float): float
</pre>
This is the integral of the reciprocal of F from 0 to t.
<p>
GNM uses PFTs for several purposes.
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
This represents a linear function $\F$ with
$\F(0)=y_0$
and
$\F(dt)=y_1$ .
Its integral is
<p>
$$ ∫_0^x F(t)dt = {ax^2}/2 + xy_0 $$

where $ a $ is the slope

$$ a = (y_1 - y_0)/t $$

and the integral of its reciprocal is

$$ ∫_0^x 1/{F(t)}dt = {\log(ax + y_0)}/a $$

<p>
<h3>2.2.2 Exponential PFT primitive</h3>
<p>
ExpCurve is a PFT primitive representing a family of exponential functions
$ F $ that vary from y0 to y1 over [0..dt].
<p>
$$ F(t) = y_0 + {(y_1-y_0)(1-e^{{Ct}/{dt}})}/(1-e^C) $$
<p>
<p>
C is a curvature parameter.
If C is positive, F is concave up,
and the change is concentrated in the later part of the interval.
If C is negative, F is concave down,
and the change is concentrated in the earlier part of the interval.
If C is zero, F is linear.
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
The definite integral of F from 0 to x is
<p>
$$ ∫_0^x F(t)dt = t*(y_0 + (y_1-y_0)*(t_{norm} * C - e^{(Ct_{norm})} + 1)/(C*(1-e^C))) $$

where

$$
t_{norm} = x/{dt}
$$
<p>
The integral of 1/F is
<p>
$$
(e^C - 1)*(tC - log(| y_0(e^C-1) + (y_1-y_0)(e^{Ct} - 1)|)) / (Cy_0(e^C-1) - (y_1-y_0)) $$
<p>
and the definite integral of 1/F from 0 to x is
<p>
$$ ∫_0^x F(t)dt = G(t) - G(0) $$



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
Represents a pause of the given duration, in units of adjusted time.
The pause shifts the start times of all subsequent events.
If <code>after</code> is True, the pause occurs
after the events at the current time;
otherwise it occurs before them.
There can be pauses of both types (before and after)
at a particular time.

<pre>
   Shift(value: float)
</pre>
Represents a shift in the adjusted times of events at the current time.
This can be used for 'agogic accents',
in which melody notes are brought out by
shifting them slightly after accompaniment notes.
Unlike Pause, subsequent events are not affected.

<h3>2.3  Transformations</h3>
<p>
The following is kinda wrong.
PFTs can be a mix.
<p>
GNM provides two general types of transformations:
<ul>
<li> Continuous:
a smooth (or piecewise smooth) change in tempo, volume,
or other parameter.
For keyboard music they affect only note starts and ends,
but conceptually they are continuous.
A given transformation could be applied to any set of notes:
whole notes, 64ths, etc.

<li> Discrete:
pauses (tempo) and accents (volume).
These occur at specific points in time.
They may occur in repeating time patterns,
at irregular times, or at single times.
</ul>
<p>
A MNS specification consists of a sequence of 'transformations'.
<p>
Each transformation consists of

<p>
<li> An 'operator' indicating the type of the transformation.
The set of operators is listed in the following section.
<li> A PFT.
<li> A note selector.

<p>
Some transformations have additional parameters.
<p>
A transformation acts on a $score, modifying it in some way.
In the following, we notate transformations
as member functions of the $score class,
where the name of the function is the operator.
<p>


<h2>3. Timing</h2>
<p>
We start by defining terms.
Let S denote score time, in units of (say) crochets, or quarter notes.
Let P denote adjusted time, measured in (say) seconds.
Assume that, at the start of a performance, both are zero.
<p>
tempo(s) = dS/dP(s); it's the rate at which score time increases
with adjusted time.  Larger means faster.
To compute event times we need the inverse of this,
slowness(s) = 1/tempo = dS/dP(t).
Larger means slower.

The time P(s) at which an event at time s occurs is
integral from 0 to s of slowness(s)

If tempo varies linearly, say tempo(t) = A + Bt,
then P(s) is integral from 0 to s of 1/(A+Bt)
This is not quadratic in s!

<p>
GNM supports three kinds of timing adjustment.
<p>
<b>Tempo control</b>: the adjusted times of note starts and
ends are changed according to a 'tempo function',
which is integrated on the intervals between events.
The tempo function can include pauses before and/or
after particular score times.
Tempo functions are represented as PFTs.
<p>
<b>Time shifting</b>.
Notes can be shifted &mdash; moved earlier or later &mdash;
in adjusted time.
Generally the duration is changed so that the end time of the note
remains fixed.
Other notes are not changed
(unlike pauses, which postpone all subsequent notes).
GNM defines several time-shift transformations:
for example,
'rolling' a chord with specified shifts for each chord note,
or using a PFT to specify varying 'agogic accents'
in which melody notes are played slightly after accompaniment notes.
<p>
<b>Articulation control:</b> Note durations
(in either score time or adjusted time)
can be scaled or set to particular values,
to express legato, portamento, and staccato.
This can be done in various ways,
including continuous variation of articulation using a PFT.

<p>
These adjustments can be layered.
For example, one could specify several layers of tempo adjustment,
followed by time shifting.

<h3>3.1 Tempo control</h3>
<p>
Tempo variation is described with a PFT.
There are three options for the meaning of the PFT:
<ul>
<li> Slowness (or inverse tempo):
The PFT value is the rate of change of adjusted time
with respect to score time.
If t0 and t1 are score times,
the PFT scales the interval from t0 to t1
by the integral of the PFT between those points.
<li> Tempo:
the PFT value is the rate of change of score time
with respect to adjusted time.
The PFT scales time by the integral of the inverse of the PFT.
<li> Pseudo-tempo:
an approximation to tempo for PFT primitive types where
it's hard to compute the integral of the inverse.
Instead, we invert the tempo parameters of the PFT primitives,
and use that as a slowness function.
</ul>
<p>
In all cases, a PFT can include momentary primitives.
These represent pauses;
their value is in units of adjusted time.
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
       bpm: bool = True
   )
</pre>
<p>
This modifies the adjusted time of the selected notes,
starting at t0,
according to the tempo function specified by the PFT.

<p>
If bpm is False, the value of the tempo function is
the rate of change of adjusted time with respect to score time.
The performance duration of a score-time interval
is the integral of the PFT over that interval.
We call this an 'inverse tempo function' because
larger values mean slower:
2.0 means go half as fast, 0.5 means go twice as fast.
<p>
If 'bpm' is True,
The PFT represents tempo rather than inverse tempo.
60 represents unity; 120 means go twice as fast, 30 means go half as fast.
<p>
In either case, the tempo function can also contain
$pause primitives, which represent a pause of a given adjusted time.
<p>
If 'normalize' is set, the tempo function is scaled
so that its average value is one;
in other words, the start and end points remain fixed in time,
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
Each event has a score time and an adjusted time.
<li> Scan this list, processing events that satisfy the note selector
(if given) and that lie within the domain of the PFT.
<li> For each pair of consecutive events E1 and E2,
compute the average A of the PFT between the score times of E1 and E2
(i.e. the integral of the PFT over this interval divided by the interval size).
<li>
Let dt be the difference in original adjusted time between E1 and E2.
Change the adjusted time of E2 to be
the (updated) adjusted time of E1 plus A*dt.
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
(adjusted time).
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
GNM provides a mechanism for specifying pedal use.
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
GNM has a mechanism called 'virtual sustain pedal'
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

<h3>4.3 Implementation and layering</h3>
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
In GNM, the volume of a note is represented by floating point number
in [0, 1] (soft to loud).
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

Multiple adjustments can result in levels > 1;
if this happens, a warning message is shown and the volume is set to 1.

<p>
The transformation
<p>
<pre>
   Score.vol_adjust_pft(
       mode: int,
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
       mode: int,
       factor: float,
       selector: Selector = None
   )
   Score.vol_adjust_func(
       mode: int,
       func: NoteToFloat,
       selector: Selector = None
   )
</pre>
These adjust the volumes of the selected notes.
For vol_adjust_func(), the 2nd argument is a function,
its argument is a $note and it returns an adjustment factor.
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
Volume transformations are typically layered (see Section X).
Multiplicative transformations commute,
so the order in which they're applied doesn't matter.
Other transformations generally do not commute.
Typically the order is:
<ul>
<li> one or more transformations with mode VOL_MULT;
<li> transformations with mode VOL_ADD;
<li> transformations with mode VOL_SET.
</ul>

<h2>7. Specifying nuance</h2>
<p>
In many applications of GNM, a human musician (composer or performer)
manually creates a nuance description for a work.
We call this process 'nuance specification'.
<p>
Developing a nuanced specification of a work is
analogous to practicing on a physical instrument.
You start by developing a mental model of a rendition:
how you want the piece to sound.
Then you create a 'rough draft' of a nuance description:
a set of transformations and their roles.
<p>
This is followed by an iterative process:
<ul>
<li> Listen to part of the rendition;
<li> Identify a deviation from the mental model;
<li> Identify and change the relevant part of the nuance description.
</ul>
<p>
This cycle may be repeated thousands of times,
so it's important that it be as easy as possible
(in terms of mouse clicks).
We discuss this in Section X below.
<p>
We created nuance specifications
for piano pieces in a variety of styles,
with the goal of creating expressive and human-like virtual performances.
In this section we describe some principles that we found useful.
But specifying nuance - like practicing for a physical performance -
is a personal process.

<h3>7.1 Nuance structure</h3>
<p>
The first step in developing a nuance specification
is to decide on a structure:
a set of layered transformations, each of which has a particular purpose.
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

<h3>7.2 Developing nuance specifications</h3>
<p>
As discussed above, the first step in developing a nuance specification
is to develop a mental model of how you want it to sound.
<p>
Next, you tag notes in the score.
If you want the left and right hands to have independent dynamics,
you could tag notes with 'lh' or 'rh'.
If you want to bring out or shape the dynamics of melodies,
you'd tag their notes.
If you want to use rubato - tempo fluctuations
in one voice but not others - you'd take those notes.
<p>
The way in which notes are tagged depends on the way
in which scores are described.
Numula has a flexible scheme for tagging notes; see section X.
<p>
The next step is to define a nuance structure.
<p>
Finally we come to the hard part:
the listen/edit cycle described earlier.
We can be a daunting task.
We found the following useful:
<ul>
<li> Work on a short part of the piece (say, one measure or phrase).
<li> Work on one voice at a time.
It may or may not be useful to hear other voices at the same time.
<li> Work on one nuance layer at a time.
It may or may not be useful to enable other layers at the same time.
</ul>

<p>
When you're done with a section, it may be useful to
make the nuance into a function (see below)
so that you can reuse it for analogous sections later in the piece.
<p>
In the course of doing low-level editing,
you may decide to make high-level changes,
e.g. to add new note tags for change the nuance structure.

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

<li> Functions:
logic - possibly parameterized,
possibly with loops and conditionals -
that generates PFTs,
or that applies transformations.

<p>
These are features of every programming language,
so we can achieve these capabilities by
wrapping GNM in a programming language:
i.e., providing an API for describing and layering transformations.
We call this 'nuance scripting'.
We have done this in Numula, using Python (see Section X).
Other languages could be used as well.
<p>
Graphical interfaces for editing nuance
could potentially be enhanced to provide scripting-like capabilities.
We believe that these are necessary for non-trivial applications.

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
Nuance layers are displayed as 'tracks' below the score,
with their PFTs shown graphically.
You can use the mouse to drag and drop nuance primitives,
adjust their parameters, and hear the results.

<p>
It would also be possible to use the nuance to modify
the way the score is displayed:
e.g. to use color or note-head size to express dynamics.

<li> Express nuance in a programming language.
This has been done in Numula (section X).
</ul>
<p>
The low-level editing cycle in Numula was cumbersome;
each adjustment required locating and editing a value in the source code,
then re-running the program
and moving the playback pointer to the relevant time.
This took a dozen or so input events (keystrokers and mouse clicks).
<p>
To accelerate low-level editing, Numula provides a feature called
'Interactive Parameter Adjustment' (IPA)
that reduces the cycle to two keystrokes.
In IPA, you can declare variables to be adjustable,
and you specify their role (tempo, volume).
<p>
You then run the program under an 'IPA interpreter'.
The interpreter lets you specify a start and end time for playback.
You can select an adjustable variable,
change its value with up/arrow keys,
and press the space bar to play the specified part of the pieces.
This reduces the editing cycle to two keystrokes.
<p>
The values of adjustable variables are stored in a file,
which is read when the interpreter is started.

<h3> 9.2 Examples</h3>
<p>
We have used Numula to create nuanced performances
of piano pieces in a variety of styles,
ranging from Beethoven to Berio.
Our goal was to create performances that approximated
performances by a skilled human.
<p>
Appassionata

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
GNM could be used in a variety of musical contexts.
For example, it could be used in combination
with a graphical score editor to generate a MIDI-based
rendition of a composition; see Figure 1.

<center>
<img src=flow.gif width=400>
<br>
Figure 1: GNM as part of a system for composition.
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
A piano teacher's instruction to a student could include
a nuance specification that guides the student's practice.
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

<h3>9.4 Automated accompaniment</h3>
<p>
A nuance specification could help an automated accompaniment
system better track a human performer.

<h3>9.5 Sharing and archival of nuance descriptions</h3>
<p>
Web sites like IMSLP [ref]
and MuseScore [ref] let people share scores.
Such sites could also host user-supplied nuance descriptions
for these works.
This would provide a framework for sharing and discussing interpretations.
<p>

<h2>6. Numula</h2>
<p>
Numula is a Python library that implements GNM.
It implements the classes listed above:
$score, $note, PFT, etc.
The transformation functions described in Sections x-y.
are member functions of the $score class.
<p>
Numula can be used in various ways.
It can be used as a stand-alone system for creating nuanced music
completely in Python.
Or it can be integrated with other systems to provide nuance capabilities;
for example, it can import a MIDI file as a $score object,
and then apply a nuance description to it.
<p>
A $score, after a nuance description has been applied,
can be output as a MIDI file.
Alternatively, Numula can play this MIDI output
using a local Pianoteq server controlled by RPCs.
<p>
Numula provides a number of textual 'shorthand notations'
for expressing both scores and different types of PFTs
(tempo, volume, pedal).
For example,
<pre>
   sh_vol('pp 2/4 mf 4/4 pp')
</pre>
returns a PFT representing a crescendo
from pp to mf over 2 beats,
then a diminuendo to pp over 4 beats.
'pp' and 'mf' are constants representing
.45 and 1.11 respectively.
<pre>
   sh_tempo('60 8/4 80 4/4 60')
</pre>
returns a PFT for a tempo that varies linearly from 60 to 80 BPM 
beats per minute) over 8 beats, then back to 60 over 4 beats. 
<pre>
   sh_pedal('1/4 (1/4 0 1.) (1/4) 4/4')
</pre>
defines a pedal that off for 1 beat,
changes linearly from off to on over 1 beat,
is on for 1 beat, then off for 4 beats.
<pre>
   sh_score('1/4 c5 d e')
</pre>
returns a Score that plays 3 quarter notes starting at middle C.
The shorthand notation scores has lots of features
that enable compact representation of complex scores;
see [ref].
<p>
All of the shorthand notations have a set of core features:
<p>
Nestable looping:
<pre>
</pre>

Parameterization (using the Python f-string feature).
Instead of hard-wiring PFT parameters,
we can make them into variables.
<pre>
med = 60
faster = 80
sh_tempo(f'{med} 8/4 {faster} 4/4 {med}')
</pre>
<p>
The contents of the {} can be any expression,
including a shorthand notation string.
<pre>
   dv1 = .7
   vm_24 = f' *2 0 1/4 {dv1] 1/4 0'
   vmeas = sh_vol(f' \
       {vmeas} 0 1/1 0 {vmeas}
   ')
<pre>

Measure checking:
<pre>
   sh_vol('
       |1
</pre>

<h2>10. Nuance inference</h2>
<p>
In the above sections, we have discussed the creation of nuance descriptions
which are applied to a score, producing a rendition.
We now consider the inverse problem:
how to 'infer' the nuance from a performance.
More precisely:
given a score and a performance of the score
(as a sound file or MIDI file)
how to find an GNM nuance description
which, when applied to the score,
closely approximates the performance.
<p>
In this section we present some ideas
on how to make this notion rigorous,
and on its possible applications.
We have not implemented any of the ideas.
<p>
First, we need to restrict the allowable nuance descriptions.
There are nuance descriptions
that exactly reproduce the performance
by specifying the time and volume of each note.
For purposes of stylistic comparison (see below)
such descriptions are not useful.
If a performance has a crescendo,
we want to represent it as a single entity.
<p>
So we need to refine the problem,
by introducing the notion of the 'complexity' of a nuance description.
The problem then becomes: for a given tolerance T,
what is the least complex nuance description
that generates the performance within that tolerance?
<p>

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

<h2>10. Related work</h2>
<h3>Time</h3>
<h3>Musical nuance</h3>
<p>
There have been considerable research into performance nuance,
some of which bears on GNM.
<p>
Statistical
<p>
physical models of rit
Trailing ritard as physical braking.
Quadratic increase in slowness.
Not clear that this fits data better than e.g. exponential.
<p>
auto generation of nuance based on structure
KTH
Todd
The resulting renditions will be generic and boring.
<p>
Clynes Superconductor
FORMULA

There has been some research in this general area.
Some papers study the statistics of deviation from the score,
but not the actual modeling of it.
<p>
Papers that model nuance.
<p>
Papers that define rules for generating nuance.
Clynes, Todd etc.

<h3>CSS</h3>
<p>
<a href=https://en.wikipedia.org/wiki/CSS>Cascading Style Sheets</a>
(CSS)
is a system for specifying the appearance of web pages.
CSS is similar to GNM in several ways:
<ul>
<li>
A CSS specification is typically separate from the web page,
which is usually described using HTML and/or Javascript.
When a CSS specification is applied to a web page,
it can change the attributes of the page's elements
(size, color, etc.) and the layout of the page.

<li> CSS files can be \"layered\".
They are applied in a particular order,
and later files can extend or override the effects of earlier ones.

<li> CSS specifications can refer to subsets of the HTML elements
using 'selectors' involving the element tags, classes, and IDs.

<li> CSS preprocessors like SASS: variables, expressions, mixins (modules).
Analogous to nuance scripting.
</ul>

<h2>11. Future work</h2>

<h3>11.3 Non-keyboard instruments</h3>
<p>
GNM could be extended to handle scores with multiple instruments.
Note tags could include the instrument type
(e.g. 'violin') and instance (e.g. 'violin 1').
<p>
GNM could be extended to describe note parameters beyond pitch and volume.
These might include
a) attack parameters such as bow weight;
b) variation in pitch, timbre or volume during a note;
these could be modeled as PFTs.

<h3>Dynamic scores</h3>
<p>
Consider the situation where a long trill (or octave tremolo)
is combined other musical material,
which is subject to timing nuance such as rubato.
Suppose the rate of the trill is fixed.
Then the number of notes in the trill is not fixed;
it varies with the timing nuance.
We call this a 'dynamic score'.
<p>
The current GNM model does not handle dynamic scores.
Doing so would require
a) evaluating the timing nuance to determine the length of the trill;
b) generating the notes in the trill,
which could itself be subject to tempo adjustment.

<p>


<h3>11.4 PFT primitives</h3>
<p>
<h3>11.5 Uses of AI</h3>
<p>
In theory one could use AI to produce nuance descriptions:
e.g. 'Play Phillip Glass in the style of Arthur Schnabel'.
That is not the goal of this work:
we want to provide tools to human composers and performers
that let them create music with their own expression.
<p>
However, AI might have useful roles.
For example, it could be used
as part of a nuance inference system.
<p>

<h2>12. Conclusion</h2>
<p>
Computers are increasingly important tools for
composition, pedagogy, and performance.

High-resolution nuance specification makes nuance a first-class citizen,
along with scores and sounds.
This will not replace the human component of nuance,
or the spontaneity of live performance;
rather, it will provide tools that can enhance these processes
<p>
Richard Kraft contributed ...

<h2>References</h2>
<p>
<ol>
<li>Malcolm Bilson.
Video: 'Knowing the Score: Do We Know How to Read Urtext Editions and How Can This Lead to Expressive and Passionate Performance?'
Cornell University Press, Ithaca, 2005.
<br>
https://www.youtube.com/watch?v=mVGN_YAX03A
<li>
Cascading Style Sheets: https://en.wikipedia.org/wiki/CSS
<li>
PianoTeq: https://en.wikipedia.org/wiki/Pianoteq
<li>
Numula: https://github.com/davidpanderson/numula/
<li>
IMSLP: https://imslp.org
<li>
MuseScore: https://musescore.com

</ol>
</div>
</body>
</html>
";

?>
