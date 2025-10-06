<?php

$score = '`Score`';
$note = '`Note`';
$measure = '`Measure`';
$nuance_desc = '`NuanceDescription`';
$pft_primitive = '`PFTPrimitive`';
$pause = '<code>Pause</code>';

// https://www.jstor.org/journal/computermusicj
// https://mathscribe.com/author/jqmath.html

// https://quillbot.com/grammar-check
// https://prowritingaid.com/grammar-checker (better)

// https://direct.mit.edu/comj/pages/submission-guidelines
// https://direct.mit.edu/DocumentLibrary/SubGuides/cmjstyle-2024-5.pdf

// expand backquotes into <code>...</code>
function expand($s) {
    $n = 0;
    $out = '';
    $start = true;
    while (1) {
        $i = strpos($s, '`', $n);
        if ($i === false) break;
        $out .= substr($s, $n, $i-$n);
        if ($start) {
            $out .= '<code>';
            $start = false;
        } else {
            $out .= '</code>';
            $start = true;
        }
        $n = $i+1;
    }
    $out .= substr($s, $n);
    return $out;
}

echo '
<html lang="en" xmlns:m="https://www.w3.org/1998/Math/MathML">

<head>
    <meta charset="utf-8">

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=UnifrakturMaguntia">
    <link rel="stylesheet" href="https://fred-wang.github.io/MathFonts/LatinModern/mathfonts.css">
    <link rel="stylesheet" href="https://fred-wang.github.io/MathFonts/STIX/mathfonts.css">
    <link rel="stylesheet" href="../mathscribe/jqmath-0.4.3.css">

    <script src="../mathscribe/jquery-1.4.3.min.js"></script>
    <script src="../mathscribe/jqmath-etc-0.4.6.min.js" charset="utf-8"></script>
    <script>M.MathPlayer = false; M.trustHtml = true;</script>
	<style>
		#mathSrc1, #mathSrc2	{ font-size: larger; vertical-align: text-bottom }
		
		table.prec-form-char	{ text-align: center }
		table.prec-form-char td:first-child	{ text-align: right }
	</style>
</head>
<body>
';
$text = "
<div style=\"margin-left: 30; width: 700; font-size:14px; font-family:Trebuchet MS; line-height:1.4\" align=justify>
<center>
<h1>Modeling performance nuance</h1>

<p>
David P. Anderson
<br>
University of California, Berkeley
<p>
October 1, 2025
</center>

<h2>Abstract</h2>

Music Nuance Model (MNM) is
a framework for describing performance nuance
(timing, dynamics, articulation, and pedaling)
in notated keyboard music.
MNM can concisely express nuance closely approximating
that of human performances.
It allows musicians &mdash; composers and performers &mdash;
to craft expressive renditions of musical works.
This capability has applications in composition, virtual performance,
and performance pedagogy.
We describe these applications,
and discuss the challenges in creating and refining
complex nuance specifications.
We also discuss the possibility of inferring nuance
from recorded human performances.

<p>

<h2>1. Introduction</h2>
<p>
This paper is concerned with \"performance nuance\" in notated music.
By this we mean the differences between a rendition of a piece
and the score on which it's based.
Nuance has a central role in Western classical music:
works in the standard repertoire
are performed and recorded thousands of times,
and nuance is the primary difference among these renditions.
<p>
We focus on keyboard instruments such as the piano.
In this context, nuance has several components:
<p>
<ul>
<li> Timing: tempo, rubato, pauses, rolled chords,
and time-shifting of notes.
<li> Dynamics: crescendos and diminuendos, accents, and chord voicing.
<li> Articulation: legato, staccato, and portamento.
<li> The use of pedals (sustain, soft, and sostenuto),
including fractional pedaling.
</ul>
<p>
For other instruments and voice,
notes have additional properties such as attack and timbre,
and these may vary during the note.
The work presented here does not address these factors
but might be extended to do so.
<p>
Some scores have nuance indications:
tempo markings, slurs, crescendo marks, fermatas, pedal markings, etc.
These indications do not completely describe the nuance in a human rendition
because
a) they're imprecise:
e.g., a fermata mark doesn't specify the durations of the sound
or of the following silence;
b) they're ambiguous:
the meaning of marks such as slurs, wedges, and staccato dots
has changed over time and varies between composers (Bilson 2005);
and c) they're incomplete:
they describe the broad strokes of the composer's intention,
but not the details of a rendition.
Indeed, Western music notation cannot express basic aspects of nuance,
such as the different volumes of notes in a chord.
<p>
In a typical human performance, nuance is guided by additional factors:
the expressive intent of the performer;
stylistic conventions (as understood by the performer);
and the performer's technique and physical limitations,
which can have an expressive role.

<p>
Music Nuance Model (MNM) is
a practical framework for describing performance nuance.
MNM has precisely defined semantics.
It can describe complex nuance in a concise way;
gestures like crescendos are described by single primitives.
MNM has other important properties:
<ul>
<li>
It can express nuance gestures that are continuous
(crescendos and accelerandos)
and/or discrete (accents and pauses).
It has a powerful mechanism,
<i>piecewise functions of time</i> (PFTs),
for describing time-varying quantities.
<li>
It can express nuance gestures ranging from long
(say, an accelerando over 32 measures)
to short (pauses in the 10 millisecond range
at the beat or measure level).
<li>
It provides a general way of selecting subsets of notes,
based either on explicit <i>tags</i>
or on note attributes such as chordal or metric position.
A <i>note selector</i> is a Boolean-valued function
of these tags and attributes.
<li>
It allows nuance to be factored into multiple layers.
Each layer, or <i>transformation</i>,
includes an operation type (e.g., tempo control), a PFT, and a note selector.
A transformation, when applied to a score,
changes parameters of some or all of the selected notes.
</ul>
<p>
The \"reference implementation\" of MNM is a Python
library called Numula (https://github.com/davidpanderson/numula/);
hence we describe MNM in terms of Python data structures and functions.
MNM could also be implemented using other languages or data representations.
It could be integrated into score editors, music programming languages,
or other music software systems.
<p>
MNM has two general areas of use.
In the first, a human musician develops a nuance description
for a score,
using an editing system of some sort.
We call this <i>nuance specification</i>.
This could be used, for example,
to create a virtual performance of a work.
The second area, <i>nuance inference</i>,
involves taking a score for a work
and a set of human performances of the work,
and finding nuance descriptions that closely approximate the performances.
This could be used for stylistic analyses and other purposes.
<p>
The remainder of the paper explores the above topics
and presents examples in which MNM was used to create virtual performances of
piano works in a range of styles.
We then discuss related and future work,
and offer conclusions.

<h2>2. The MNM model</h2>

<h3>2.1 Time</h3>
<p>
MNM uses two notions of time:
<p>
<i>Score time</i>: time as notated in a score,
represented as floating-point numbers.
The scale is arbitrary;
our convention is that the unit is a 4-beat measure.
Thus, the duration of a quarter note is 1/4, or 0.25.
<p>
 <i>Adjusted time</i>: a transformed version of score time.
In the final result of applying an MNM description to a score,
adjusted time is real time, measured in seconds.

<p>

<h3>2.2 Scores</h3>
<p>
MNM involves several abstract classes;
in Numula, these are Python classes.
<p>
The class $score represents the basic parts of a musical work:
note pitches and notated timings, and measure boundaries if present.
A $score could be derived from a MusicXML file,
a Music21 object hierarchy, or a MIDI file;
or it could be generated programmatically.

<p>
The class $note represents a note.
Notes have various <i>attributes</i>.
Some attributes of a note `N` are derived from the score:
the start time and duration in score time (`N.time` and `N.dur`),
the adjusted start time and duration (`N.adj_time` and `N.adj_dur`),
and the pitch `N.pitch` (represented, for example, as a MIDI pitch number).

<p>
Other note attributes are defined as part of a nuance specification.
Notes have an attribute `N.tags`, a set of textual <i>tags</i>.
Note attributes and tags provide a way to specify
the set of notes to which a nuance gesture is to be applied.
This is done using <i>note selector</i> functions, described below.

<p>
The class $measure represents a measure.
Each measure has a start time and duration,
in units of score time.
Measures must be non-overlapping.
Like Notes, Measures can have tags.
By convention, these tags include a string representing the measure's
duration and metric structure.
For example, the tag `2+2+3/8` might represent a 7/8 measure
grouped as 2+2+3 eighths.
<p>

<h3>2.3 Note attributes and tags</h3>
<p>
Note attributes, including tags, can have various sources.
First, some attributes of a $note `N` are automatically assigned,
based on its context in the score:
<p>
<ul>
<li> Tags `top` or `bottom` are added if N
has the highest or lowest pitch of notes starting at the same time.
<li> `N.nchord` is the number of notes
with the same start time as N, and `N.nchord_pos`
is N's pitch order in this set (0 = lowest, 1 = 2nd lowest, etc.).
</ul>
<p>
If a note N lies within a measure M, N has two additional attributes:
<ul>
<li> `N.measure`: a reference to M.
<li> `N.measure_offset`: N's time offset from the start of M.
</ul>
<p>
If a note lies on the boundary between two measures,
it's considered to be in the second one.
</ul>
<p>
Second, if the score has information such as
slurs, accent marks, dynamic markings, and note stem directions,
these could be used to automatically assign attributes.
<p>
Finally, the user can explicitly assign attributes and tags
as part of specifying nuance.
For example:
<ul>
<li> Tags could be used to indicate notes in the left and right hands
of a piano piece.
<li>
In a fugue, tags could indicate that a note is part of the fugue theme,
or of a particular instance of the theme.
<li>
Tags could indicate the harmonic function of notes;
e.g., a note in a dominant chord in a cadence,
or the 7th in a major seventh chord.
<li>
Attribute and tags could used to identify hierarchical
structural components of a work.
For example, one could tag notes in the development section of a Sonata.
</ul>
<p>
MNM does not specify or restrict how attributes and tags are assigned.
This could be done manually by a human nuance creator,
or automatically by the software system in which MNM is embedded.

<h3>2.4 Note selectors</h3>
<p>
A <i>note selector</i> is a Boolean-valued function of a note.
A note selector $ F $ identifies a set of notes within a $score,
namely the notes $ N $ for which $ F(N) $ is true.
We use Python syntax for these expressions.
For example, the function
<p>
<pre>
    lambda n: 'rh' in n.tags and n.dur == 1/2
</pre>
<p>
selects all half notes in the right hand, and
<pre>
    lambda n: '3/4' in n.measure.tags and n.measure_offset == 2/4
</pre>
selects notes on the 3rd beat of 3/4 measures.
One could select notes based on their score time,
their pitch, or any combination of note attributes.
<p>
In Python, the type of note selectors is
<pre>
    type NoteSelector = Callable[[$note], bool] | None
</pre>
<p>

<h3>2.5 Piecewise functions of time</h3>
<p>
Nuance gestures typically involve values
(such as tempo and volume) that change over time.
In MNM, these are described as functions of score time.
They are specified as a sequence of <i>primitives</i>,
each of which describes a function defined
either on a time interval or at a point.
A function defined in this way is called a <i>piecewise function of time</i>
(PFT).
<p>
In the reference implementation of MNM,
PFT primitives are objects with types derived from a base class $pft_primitive.
There are two kinds of PFT primitives:
<p>
<i>Interval primitives</i>
describe a function over a time interval $ [0, dt] $ where $ dt > 0 $.
Examples:
<pre>
    class Linear(PFTPrimitive)          # linear function
    class ShiftedExp(PFTPrimitive)      # shifted exponential function
</pre>
Primitives could be defined for other types of functions
(polynomial, trigonometric, spline, etc.).

<p>
<i>Momentary primitives</i> represent a value at a single moment.
Examples:
<pre>
    class Accent(PFTPrimitive)          # an accent (volume)
    class Pause(PFTPrimitive)           # a pause (timing)
    class Shift(PFTPrimitive)           # a time shift (timing)
</pre>
<p>
A PFT is represented by lists of PFT primitives:
<pre>
    type PFT = list[PFTPrimitive]
</pre>

<p>
<p>
MNM uses PFTs for multiple purposes, such as tempo, volume,
time shifts, and pedaling.
When a PFT is used to describe tempo,
the definite integral of the function or its reciprocal is needed.
Thus, primitives used in tempo PFTs must provide member functions
<pre>
    integral(t: float): float           # the integral of F from 0 to t
    integral_reciprocal(t: float): float    # the integral of 1/F from 0 to t
</pre>
<p>
When a PFT is used for purposes other than tempo,
the function value is used.
If there is a discontinuity in the PFT
(i.e. the ending value of a primitive differs
from the starting value of the next primitive),
one must specify which of these values is used.
Primitives used for these purposes must provide member functions
<p>
<pre>
    value(t: float): float              # value at time t
    closed_start(): bool                # closure at time 0
    closed_end(): bool                  # closure at time dt
</pre>
<p>
For example, a volume function might be defined by the PFT:
<pre>
   [
       Linear(25, 15, 2/1, closed_start = True),
       Linear(15, 20, 1/1, closed_end = True),
       Linear(10, 15, 2/1, closed_start = False)
   ]
</pre>
<p>
This defines a function that varies linearly
from 25 to 15 over two 4-beat measures,
from 15 to 20 over one measure,
then from 10 to 15 over two measures.
Its value at the start of the 4th measure is 20
because of the closure arguments;
see Figure 1.
<p>
<center>
<img src=pft.svg width=500>
<br>
<b>
Figure 1: A piecewise function of time is a concatenation of primitives.
Closure determines the function value at discontinuities.
</b>
</center>
<p>

<h3>2.5.1 Linear PFT primitive</h3>
<p>
The `Linear` primitive represents a linear function $\F$ with
$\F(0)=y_0$
and
$\F(Δt)=y_1$ .
Its definite integral is
<p>
$$ ∫_0^x F(t)dt = {ax^2}/2 + xy_0 $$

<p>
where $ a $ is the slope

$$ a = {y_1 - y_0}/t $$

<p>
and the definite integral of its reciprocal is
<p>
$$ ∫_0^x 1/{F(t)}dt = {\log(ax + y_0)-\log(y_0)}/a $$

<p>
<h3>2.5.2 Shifted exponential PFT primitive</h3>
<p>
`ShiftedExp` is a PFT primitive representing a family of
\"shifted exponential\" functions
$ F(t) $ that vary from $ y_0 $ to $ y_1 $ over $ [0, Δt] $.
<p>
$$ F(t) = y_0 + {(y_1-y_0)(1-e^{{Ct}/{Δt}})}/{1-e^C} $$
<p>
<p>
C is a curvature parameter.
If C is positive, F is concave up,
and the change is concentrated in the later part of the interval.
If C is negative, F is concave down,
and the change is concentrated in the earlier part of the interval.
If C is zero, F is linear.
Figure 2 shows examples.
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
<b>
Figure 2: Shifted-exponential primitives with different curvatures.
</b>
</center>

`ShiftedExp` is quite versatile.
In developing nuance descriptions for a range of piano pieces,
we found that `ShiftedExp` (and `Linear`, a special case) were sufficient
for expressing the desired continuous variations in both tempo and volume.
<p>
The definite integral of $ F $ from 0 to $ x $ is
<p>
$$ ∫_0^x F(t)dt = t(y_0 + {Δy(t_{norm} C - e^{(Ct_{norm})} + 1)}/{C(1-e^C)}) $$

where

$$
t_{norm} = x/{Δt}
$$
<p>
$$
Δy = y_1 - y_0
$$
<p>
The indefinite integral of $ 1/F $ is
<p>
$$
G(t) = ∫ 1/{F(t)}dt = {(e^C - 1)(tC - log(|\; y_0(e^C-1) + Δy(e^{Ct} - 1)|))} / {Cy_0(e^C-1) - Δy} + constant $$
<p>
and hence the definite integral of $ 1/F $ from 0 to $ x $ is
<p>
$$ ∫_0^x 1/{F(t)}dt = G(x) - G(0) $$

<h3>2.5.3 Momentary PFT primitives</h3>
<p>
MNM defines several momentary primitives, used for different purposes.
<pre>
    Accent(value: float)
</pre>

This represents a volume adjustment for notes starting at a particular time.
The surrounding interval segments must be open at their respective ends.

<pre>
    Pause(duration: float, after: bool)
</pre>
This is used in tempo PFTs to represent a pause of the given duration,
in units of adjusted time.
If `after` is True, the pause occurs after the events at the current time;
otherwise it occurs before them.
There can be pauses both before and after a given time.

<pre>
    Shift(value: float)
</pre>
This represents a shift in the adjusted times of events at the current time;
it does not affect later events.
This can be used for \"agogic accents\"
in which melody notes are brought out by
shifting them slightly before or after accompaniment notes.

<h3>2.6  Transformations</h3>
<p>
An MNM specification comprises a sequence of <i>transformations</i>.
A transformation acts on a $score, modifying it in some way.
Each transformation includes an \"operator\"
indicating the type of the transformation.
We notate transformations as member functions of the $score class;
each function corresponds to an operator.
These functions are listed in the following sections.
<p>
These functions have various parameters.
Most include a PFT describing a time-varying quantity,
and a time offset $ t_0 $ indicating the score time
at which the transformation starts.
Most have an optional note selector, indicating what notes are affected.
<p>
Some tranformations have a function argument mapping a $note to a number
(for example, a volume adjustment factor).
These arguments have the Python type
<pre>
    type NoteToFloat = Callable[[Note], float]
</pre>

<h2>3. Timing</h2>
<p>
MNM supports three kinds of timing adjustment.
<p>
<b>Tempo control</b>: The adjusted times of
events (note and pedal starts and ends)
are changed according to a <i>tempo function</i>,
represented as a PFT.
<p>
<b>Time shifting</b>.
Note starts can be shifted earlier or later in adjusted time.
Other notes are not changed
(unlike pauses, which postpone all subsequent notes).
<p>
<b>Articulation control:</b> Note durations
(in either score time or adjusted time)
can be scaled or set to particular values,
to express legato, portamento, or staccato.
This can be done in various ways,
including continuous variation of articulation using a PFT.

<p>
These adjustments can be layered.
For example, one could specify several layers of tempo adjustment,
followed by time shifting.

<h3>3.1 Tempo control</h3>
<p>
Tempo variation is described with a PFT.
MNM provides three options for the meaning of this PFT:
<ul>
<li> <b>Slowness</b> (or inverse tempo):
The PFT value is the rate of change of adjusted time
with respect to score time.
If $ t_0 $ and $ t_1 $ are score times,
the PFT scales the interval from $ t_0 $ to $ t_1 $
by the average value of the PFT between those times.
Thus, larger means slower.
<li> <b>Tempo</b>:
the PFT value is the rate of change of score time
with respect to adjusted time.
Intervals are scaled
based on the integral of the reciprocal of the PFT;
larger means faster.
<li> <b>Pseudo-tempo</b>:
an approximation to tempo for PFT primitive types for which
it's infeasible to compute the integral of the reciprocal.
Instead, the tempo parameters of the PFT primitives are inverted,
and the result is used as a slowness function.
</ul>
<p>
In all cases, the PFT can include `Pause` primitives.
They are like Dirac delta functions, with zero width but nonzero integral.
The start times of later events are shifted by the pause duration.
In the case of a \"before\" pause at a time $ t $,
notes that start before $ t $ and are sounding at $ t $ are elongated
to avoid introducing gaps in the sound.
<p>
The following transformation modifies the adjusted time of the selected notes,
starting at score time $ t_0 $,
according to the tempo function specified by the PFT,
acting in the given mode:
<pre>
    TIME_TEMPO = 1
    TIME_PSEUDO_TEMPO = 2
    TIME_SLOWNESS = 3

    Score.tempo_adjust_pft(
        pft: PFT,
        t0: float,
        selector: NoteSelector,
        normalize: bool,
        mode: int           # one of the above modes
    )
</pre>
<p>
If `normalize` is set, the tempo adjustment is scaled
so that its average value is one;
in other words, the adjusted times of the start and end points remain fixed,
but events between them can move.
This can be used, for example, to apply rubato to
a particular voice over a given period,
then have the voice synch up with other voices at the end of that period.
<p>
For example, in a rendition Chopin's Nocturne no. 1
(see Figure 3),
we applied a tempo adjustment
consisting of an accelerando, a ritardando, and two small pauses
to the right-hand flourish.
This adjustment was normalized so that the left and right hands
synch up at the end of the figure.
<center>
<img width=600 src=chopin.jpg>
<p>
<b>
Figure 3: Example from Chopin's Nocturne no. 1.
</b>
</center>
<p>
The implementation of `tempo_adjust_pft()`, somewhat simplified,
is as follows (see Figure 4):
<p>
<ul>
<li> Make a list of all 'events' (starts and ends of selected notes
and pedal applications)
ordered by score time.
Each event has a score time and an adjusted time.
<li> Scan this list, processing events 
that lie within the domain of the PFT.
<li> For each pair of consecutive events E1 and E2,
compute the average A of the PFT between the score times of E1 and E2
(i.e., the integral of the PFT over this interval divided by the interval size).
<li>
Let dt be the difference in original adjusted time between E1 and E2.
Change the adjusted time of E2 to be
the (updated) adjusted time of E1 plus A*dt.
</ul>
<center>
<img src=tempo.svg width=600>
<br>
<b>Figure 4: Example of tempo adjustment.
The interval between events E1 and E2 is scaled by the average value of
the slowness (inverse tempo) function between their score times.</b>
</center>
<p>

<h3>3.2 Time shifts</h3>
<p>
These transformations modify the adjusted start times of notes,
and change their durations to preserve the end times.
<p>
The following transformation,
for notes N that satisfy the selector and lie in the domain of the PFT,
adds `pft.value(N.time - t0)` to `N.adj_time`:
<pre>
    Score.time_shift_pft(
        pft: PFT,
        t0: float = 0,
        selector: NoteSelector
    )
</pre>
This can be used to give agogic accents to notes at particular times
or to shift notes by continuously-varying amounts.
<p>
The following transformation \"rolls\" the chord at the given time.
<pre>
    Score.roll(
        t: float,
        offsets: list[float],
        is_up: bool = True,
        selector: NoteSelector
    )
</pre>
<p>
The `offsets` argument is a list of time offsets.
These offsets are added to the adjusted start times of notes
that start at score time `t`.
If `is_up` is true, the offsets are applied from the bottom pitch upwards;
otherwise they are applied from the top pitch downward.
<p>
The following transformation adds offsets
to the adjusted start times of notes satisfying the selector,
in time order.
<pre>
    Score.t_adjust_list(
        offsets: list[float],
        selector: NoteSelector
    )
</pre>
<p>
The `offsets` argument is a list of adjusted-time offsets.
<p>
The following transformation adds
adjusted-time offsets given by a function of the note:
<pre>
    Score.t_adjust_func(
        f: NotetoFloat,
        selector: NoteSelector
    )
</pre>
<p>
For each note N satisfying the selector,
this adds `f(N)` to `N.adj_time`.
For example, the following adds Gaussian jitter to note start times:
<pre>
    s.t_adjust_func(lambda n: .005*numpy.random.normal(), None)
</pre>
<p>
Adding such offsets to note times and volumes
can make renditions sound more \"human\".

<h3>3.3. Articulation</h3>
<p>
These transformations modify the adjusted-time duration of notes.
<p>
The following transformation multiplies
the duration of the selected notes `N` by `f(N)`.
<pre>
    Score.perf_dur_rel(
        f: NotetoFloat,
        selector: NoteSelector
    )
</pre>
<p>
The following transformation sets the duration of selected notes `N` to `f(N)`.
<pre>
    Score.perf_dur_func(
        f: NotetoFloat,
        selector: NoteSelector
    )
</pre>

<h3>3.4 Layering timing transformations</h3>
<p>
PFT-based tempo transformations without pauses commute,
so the order in which they're applied doesn't matter.
Other transformations generally don't commute.
A typical order of transformations:
<ul>
<li> Non-pause tempo transformations.
<li> Pause transformations.
<li> Shift transformations.
<li> Articulation transformations.
</ul>

<h2>4. Pedal control</h2>
<h3>4.1 Standard pedals</h3>
<p>
Grand pianos typically have three pedals:
<ul>
<li> <b>Sustain pedal</b>: when fully depressed,
the dampers are lifted so that
notes continue to sound after their key is released,
and all strings vibrate sympathetically.
If the pedal is gradually raised, the dampers are gradually lowered;
pianists use this 'half pedaling' (or more generally, fractional pedaling)
to create various effects.
<li> <b>Sostenuto pedal</b>: like the sustain pedal,
but when it is depressed, only the dampers
of currently depressed keys remain lifted.
Half-pedaling works similarly to the sustain pedal.
<li> <b>Soft pedal</b>: the hammers are shifted so that they
hit only 2 of the 3 strings of treble notes.
This reduces loudness and typically softens the timbre.
Fractional pedaling can also be used; its effects vary between pianos.
</ul>
<p>
Pedaling, including fractional pedaling,
is critical to the sound of most performances,
but few composers notate it at all, much less completely and precisely.
Notation of fractional pedal is rare.
<p>
Some MIDI piano synthesizers implement all three pedal types;
a few also implement fractional pedaling.
For example, Pianoteq (https://en.wikipedia.org/wiki/Pianoteq) does both.
<p>
MNM provides a mechanism for specifying pedal use.
The level of a particular pedal can be specified as a PFT
consisting of `Linear` primitives with values in $ [0,1] $,
where 1 means the pedal is fully depressed and 0 means it's lifted.
<p>
When a pedal change is simultaneous with note starts,
we need to be able to specify
whether the change occurs before or after the notes are played.
For sustain and sostenuto pedals,
we also need to be able to specify momentary lifting of the pedal.
MNM handle these requirements using the closure attributes
of PFT primitives.
Suppose that P0 and P1 are consecutive primitives;
P0 ends and P1 begins at time t,
and one or more notes start at t.
The semantics of the PFT depend on the closure of P0 and P1 as follows
(`P1.y0` denotes the initial value of P1):
<p>
<pre>
end of P0   start of P1     Semantics
-------------------------------------
open        open            lift pedal, play notes, pedal=P1.y0
open        closed          lift pedal, pedal=P1.y0, play notes
closed      open            play notes
closed      closed          play notes, pedal=P1.y0
</pre>

<p>
The `Linear` primitive allows expression of
continuously-changing fractional pedal.
For example,
<pre>
    Linear(4/4, 1.0, .5)
</pre>
represents a gradual pedal change from fully depressed to half depressed
over 4 beats.
If MNM is being used to generate MIDI output, this produces
a sequence of continuous-controller commands
with values ranging from 127 to 64.

<p>
The following transformation applies a pedal of the given type,
with values described by a PFT, to a $score starting at score time $ t_0 $:
<pre>
    PEDAL_SUSTAIN = 1
    PEDAL_SOSTENUTO = 2
    PEDAL_SOFT = 3
    
    Score.pedal_pft(
        pft: PFT,
        type: int,  # one of the above pedal types
        t0: float
    )
</pre>

<h3>4.2 Virtual sustain pedals</h3>
<p>
Sometimes it's musically useful to sustain only certain keys (pitches).
The sustain pedal can't do this: it affects all keys.
The sostenuto pedal affects a subset of keys,
but its semantics limit its use.
MNM has a mechanism, <i>virtual sustain pedal</i>,
that is like a sustain pedal that affects only a specified set of notes.

<p>
The use of a virtual sustain pedal
is specified by the same type of PFT as for standard pedals,
but the only allowed values are 0 (pedal off) or 1 (pedal on).
Such a PFT is applied to a score with
<pre>
    Score.vsustain_pft(
        pft: PFT,
        t0: float,
        selector: NoteSelector
    )
</pre>
If a note N is selected,
and the virtual pedal is on at its start time,
`N.dur` is adjusted so that N is sustained at least until the
pedal is released.
<p>
Virtual sustain pedals can be used,
for example, to sustain an accompaniment figure
without affecting the melody.
In the Chopin example in Figure 1,
we could use a virtual sustain pedal to sustain the chords in the left hand
without blurring the right-hand melody.
This would be impossible in a physical performance.

<p>
Compared to standard sustain pedals,
virtual sustain pedals are more flexible
in terms of what notes are sustained.
They lack two features of standard pedals: there is no fractional pedal,
and there is no sympathetic resonance of open strings.

<h3>4.3 Pedal layering</h3>
<p>
In an MNM nuance description,
pedal specifications must precede timing adjustments
so that pedal timing is correct.
Timing adjustments (including time shifts)
affect pedal usage as well as notes.
For virtual pedals this happens automatically.
For standard pedals, if a note at time T is shifted backward in time,
pedals active at T are shifted backward by the same amount.

<p>
Uses of the standard pedals can't be layered;
that is, two PFTs controlling a particular pedal can't overlap in time.
However, virtual sustain PFTs can overlap standard pedal PFTs.

<h2>5. Dynamics</h2>
<p>
In MNM, the volume of a note is represented by floating-point number
in [0..1] (soft to loud).
This may be mapped linearly to a MIDI velocity (0..127);
the perceived loudness depends on the synthesis engine and other factors.
Notes initially have volume 0.5.
<p>
MNM provides three modes of volume adjustment.
In each case there is an adjustment factor A,
which may vary over time.

<ul>
<li> `VOL_MULT`: the note volume is multiplied by A,
which typically is in [0..2].
This maps the default volume 0.5 to the full range [0..1].
These adjustments are commutative.

<li> `VOL_ADD`: A is added to the note volume.
A is typically around .1 or .2.
This is useful for bringing out melody notes when the overall volume is low.

<li> `VOL_SET`: the note volume is set to A/2.
A is in [0, 2].
The division by 2 means that the scale is the same as for `VOL_MULT`.

</ul>

Multiple volume adjustments can result in levels greater than one
or less than zero,
in which case a warning is generated and the volume is truncated.

<p>
The following transformation adjusts the volume of selected notes
according to a function of time specified by a PFT:
<p>
<pre>
    Score.vol_adjust_pft(
        mode: int,          # one of the above modes
        pft: PFT,
        t0: float,
        selector: NoteSelector
    )
</pre>
<p>
The volume of a selected note `N`
in the domain of the PFT is adjusted by the factor given
by the value of the PFT at time `N.time - t0`.
<p>
This can be used to set the overall volume of the piece
or to shape the dynamics of an inner voice by selecting
the tag used for that voice.
<p>
Other transformations adjust note volumes directly,
without a PFT:
<pre>
    Score.vol_adjust(
        mode: int,
        factor: float,
        selector: NoteSelector
    )
    Score.vol_adjust_func(
        mode: int,
        func: NoteToFloat,
        selector: NoteSelector
    )
</pre>
These adjust the volumes of the selected notes.
For `vol_adjust_func()`, the 2nd argument is a function;
its argument is a $note and it returns an adjustment factor.
For example,
<p>
<pre>
    score.vol_adjust_func(VOL_ADD, lambda n: .01*numpy.random.normal(), None)
</pre>
<p>
makes a small normally distributed adjustment to the volume of all notes.
<p>
In a piece with 4/4 measures,
the following transformations de-emphasize notes on weak beats:
<pre>
    score.vol_adjust(VOL_MULT, .9, lambda n: n.measure_offset == 2)
    score.vol_adjust(VOL_MULT, .8, lambda n: n.measure_offset in [1,3])
    score.vol_adjust(VOL_MULT, .7, lambda n: n.measure_offset not in [0,1,2,3])
</pre>
<p>

<h3>5.1 Layering volume transformations</h3>
<p>
Volume transformations can be layered.
Multiplicative transformations commute, so their order doesn't matter.
Other transformations generally do not commute.
A typical order:
<ul>
<li> one or more transformations with mode `VOL_MULT`;
<li> transformations with mode `VOL_ADD`;
<li> transformations with mode `VOL_SET`.
</ul>

<h2>6. The process of specifying nuance</h2>
<p>
MNM was designed to allow a human musician (composer or performer)
to manually create a nuance description for a work.
We call this process <i>nuance specification</i>.
It's analogous to practicing the work on a physical instrument.
You start by forming a mental model of how you want the piece to sound.
You create a \"rough draft\" of a nuance description.
Then you iteratively edit the description
to bring it closer to your mental model
(which may evolve in the process).
<p>
We created nuance specifications for piano pieces in several styles,
with the goal of creating expressive and human-like virtual performances
(see \"Examples\" below).
In this section we describe some principles and techniques that we found useful.

<h3>6.1 Note tagging</h3>
<p>
The first step in creating a nuance specification
is to identify sets of notes that are to be treated specially,
and to assign corresponding tags to those notes.
For example, one could tag notes as being melody or accompaniment,
or as being in the left- or right-hand part.
Notes can have multiple tags, so these sets can overlap.

<h3>6.2 Nuance structure</h3>
<p>
The next step is to decide on a <i>nuance structure</i>:
a sequence of transformation types, each with a particular purpose.
The goal is that when one wants to change something,
it's clear which layer is involved.
<p>
We generally use, for both timing and dynamics:
<ul>
<li> One or more layers of continuous transformation:
typically a layer at the phrase level (1-8 measures or so)
and a layer at a shorter time scale (1-4 beats).
<li> A layer of repeating discrete change
(for example, patterns of accents on the beats within a measure,
or pauses within a measure).
<li> A layer of irregular discrete change
(for example, pauses at phrase ends
or agogic accents on particular melody notes).
</ul>

<p>
Typically some layers apply only to note subsets:
for example, the left and right hand parts,
or accompaniment and melody.
This is done by tagging these subsets and using note selectors.
<p>
For pedal control, we typically use:
<ul>
<li> A PFT for the standard sustain pedal.
<li> PFTs for virtual sustain pedals affecting only some voices
(e.g. left or right hand).
</ul>

<h3>6.3 Refining nuance specifications</h3>
<p>
Given a nuance structure, one can make a \"rough draft\"
based on score markings and intuition.
This is followed by an iterative refinement process.
At a low level, this involves an editing cycle:
<ul>
<li> Listen to part of the rendition.
<li> Identify a deviation from the mental model.
<li> Locate and change the relevant part of the nuance description:
for example, a parameter of a PFT primitive.
</ul>
<p>
This cycle may be repeated thousands of times,
so it should be as streamlined as possible.
We have found that one continues to edit nuance
only as long as the reward exceeds the effort.

<p>
One needs a high-level strategy as well.
We found the following guidelines useful:
<ul>
<li> Work on a short part of the piece (say, one measure or phrase).
<li> Work on one voice at a time.
It may be useful to hear other voices at the same time.
<li> Work on one nuance layer at a time.
It may be useful to enable other layers at the same time.
</ul>

<p>
When done editing a section,
one can collect the transformations into a function (see below)
that can be reused in similar sections later in the piece.
<p>
High- and low-level editing are intertwined.
In the course of doing low-level editing,
one may decide to make high-level changes,
such as adding note tags or changing the nuance structure.
<p>

<h2>7. Nuance scripting</h2>
<p>
In developing nuance specifications for long and complex pieces,
it's useful to be able to express:

<ul>
<li> <b>Repetition</b>:
one might want to define a dynamic pattern
and apply it 16 times in a row,
rather than repeating the definition 16 times.

<li> <b>Parameterization</b>:
instead of hard-coding values such as PFT primitive parameters,
one might want to use variables,
so that a single change can affect many places.

<li> <b>Functions</b>:
one might want to express PFTs or sets of transformations
with functions, possibly using parameters,
loops, conditionals, recursion, and so on.
</ul>
<p>
We call these features <i>nuance scripting</i>.
In our experience, scripting is necessary for complex applications.
The features are found in all programming languages,
so the capabilities can be achieved by
wrapping MNM in a programming language:
i.e., developing an API for describing and layering transformations.
We did this in Numula using Python;
other languages could be used as well.
<p>

<h2>8. User interfaces for editing nuance</h2>
<p>
We now discuss possible user interfaces for creating
and editing MNM nuance specifications.
Desiderata for such interfaces include:
<ul>
<li> access to all MNM features:
PFTs, transformations, note selectors, and so on;
<li> support for nuance scripting;
<li> ease of use; in particular, an efficient low-level editing cycle.
</ul>
<p>
We propose four general approaches:
graphical, textual, demonstrative, and motile.
<p>
<b>Graphical</b>:
Nuance transformations could, for example,
be displayed as 'tracks',
with their PFTs shown graphically as functions of time.
The mouse is used to drag and drop nuance primitives,
and to adjust their parameters.
This could be integrated with a graphical score editor
such as Musescore or Sibelius;
transformations would be displayed underneath the
corresponding part of the score.
The interface could also convey nuance in the way the score is displayed:
for example, note-head color or size could express dynamics,
and horizontal position could indicate adjusted time.

<p>
Making a graphical interface scriptable is a challenge.
The interface could, perhaps, allow copy-and-paste of units of nuance
such as dynamic shapes.
But it would need to allow these copies to be linked,
so that a change in one is automatically propagated to the others.
Features like iteration and functions
would require either a scripting language,
as is found in mostly-graphical systems like Max (Puckette 2002),
or a graphical programming language like Scratch (Resnick 2009).

<p>
<b>Textual</b>:
For example, MNM could be presented as an API in a programming language;
Numula uses Python for this purpose.
The user describes nuance by writing a program.
The system could potentially also allow programmatic description of scores.
Scriptability is inherent in this approach.
Alternatively, an existing system for textual score specification,
such as Lilypond (Nienhuys 2003),
could be extended to include nuance and to be scriptable.
<p>
Ease of use is a challenge for textual systems.
First, if we use the native syntax of a programming language
(data structure declarations and function calls)
the amount of typing can be prohibitive.
Numula addresses this by defining
textual shorthand notations for various purposes,
such as volume and tempo PFTs.
The second issue is the efficiency of the editing cycle.
If the user has to scroll through a text file,
locate and edit some text, and then re-run a program,
this adds up to perhaps a dozen input actions.
This is cumbersome; it can lead to
a mental state in which musical focus
is displaced by syntactic issues.
Numula addresses this issue, in part,
using a feature in which parameter adjustment
and playback are done with single keystrokes.

<p>
<b>Demonstrative</b>:
the user inputs nuance gestures by
performing them on a computer-interfaced instrument or singing them.
For example, one might play a melody,
and the system would capture the tempo and volume contours,
representing them as MNM transformations.

<p>
<b>Motile</b>:
the user inputs nuance gestures by \"conducting\" them in some way,
perhaps using a mouse, touch screen,
or a baton-like input device.
<p>
Each of these approaches has strengths and weaknesses.
They can potentially be combined.
For example, demonstrative and motile interfaces are
probably best for capturing large-scale nuance gestures with coarse resolution;
the results could then be refined using a graphical or textual interface.

<h2>9. Applications of nuance specification</h2>
<p>
Nuance specification has several potential applications.
<p>
<b>Composition</b>:
As a composer writes a piece,
perhaps using a score editor such as MuseScore or Sibelius,
they could also develop a nuance specification for the piece.
The audio rendering function of the score editor could
use this to produce nuanced renditions of the piece.
This would facilitate the composition process
and would convey the composer's musical intentions
to prospective performers.

<p>

<b>Virtual performance</b>:
Musicians could create nuanced virtual performances
in which pieces are rendered using a computer
rather than a physical instrument.
Compared to physical performance, this has several advantages:
performers are not limited by their physical capabilities,
they can return to working on a piece
without having to relearn it,
and multiple performers can collaborate on a rendition.

<p>
<b>Performance pedagogy</b>:
A piano teacher's instruction to a student could include
a nuance specification that guides the student's practice.
Feedback could be given in various ways.
For example, as a student practices a piece, they could see a
'virtual conductor' that shows, on a screen,
a representation of the target nuance.
Or a 'virtual coach' could make suggestions
(musical and/or technical) to the student based on
the differences between their playing and the nuance specification.

<p>
<b>Ensemble rehearsal and practice</b>:
When an ensemble (say, a piano duo) rehearses together,
they could record their interpretive decisions as a nuance specification.
They could then use this to guide their individual practice
(perhaps via a 'virtual conductor' as described above).

<p>
<b>Musical collaborations</b>:
A dance troupe or musical theater group might
not be able to afford live musicians for rehearsals.
Instead, the group could develop a specification of the nuance they want,
use it to synthesize music for rehearsals,
then give it to the musicians to prepare for performance.

<p>
<b>Automated accompaniment</b>:
a computer system could better track a human performer
given an approximate description of the nuance
the performer is likely to use.

<p>
<b>Sharing and archival of nuance descriptions</b>:
Web sites like IMSLP (https://imslp.org)
and MuseScore (https://musescore.com) let people share scores.
Such sites could also host user-supplied nuance descriptions,
providing a framework for sharing and discussing interpretations.
<p>

<h2>10. Numula</h2>
<p>
Numula is a Python library for creating nuanced music.
It consists of several modules; see Figure 5.
These modules can be used separately or in combination,
and they could be integrated into other systems.

<center>
<img src=numula.svg>
<b>Figure 5: The components of Numula.</b>
</center>
<p><br><p>
Numula defines the classes listed earlier in this paper:
$score, $note, PFT, etc.
The transformation functions described in earlier sections
are members of the $score class;
they form the \"MNM engine\".
A $score, after nuance transformations, can be output as a MIDI file.
For convenience, Numula can be configured to play MIDI output
using a Pianoteq server controlled by RPCs.
<p>
Numula can be used as a stand-alone system for creating nuanced music.
Alternatively it can provide nuance capabilities to other systems:
it can import a MIDI file as a $score object,
apply a nuance description to it,
and output the result as a MIDI file.
<p>

<h3>10.1 Shorthand notations</h3>
<p>
Numula provides textual <i>shorthand notations</i>
for describing scores and various types of PFTs
(tempo, volume, pedal, and so on).
These notations require much less typing (and time) than
describing the scores and PFTs directly in Python.
Furthermore, shorthand notations eliminate the need to write Python code,
making Numula usable by non-programmers.
In the example pieces described below,
the Numula code is almost entirely shorthand strings.
<p>
Each type of shorthand notation has its own syntax.
<p>
<b>Volume PFTs</b>:
<pre>
    sh_vol('pp 2/4 mf 4/4 pp')
</pre>
returns a PFT representing a crescendo
from <i><b>pp</b></i> to <i><b>mf</b></i> over 2 beats,
then a diminuendo to pianissimo over 4 beats
(`pp` and `mf` are constants representing .45 and 1.11 respectively).
<p>
<b>Tempo PFTs</b>:
<pre>
    sh_tempo('60 8/4 80 p.03 4/4 60')
</pre>
returns a PFT for a tempo that varies linearly from 60 to 80 BPM
over 8 beats,
a pause of 30 milliseconds after that point,
then linearly back to 60 BPM over 4 beats. 
<p>
<b>Pedal PFTs</b>:
<pre>
    sh_pedal('1/4 (1/4 0 1.) (1/4) 4/4')
</pre>
defines a pedal that's off for 1 beat,
changes linearly from off to on over 1 beat,
is on for 1 beat, then off for 4 beats.
<p>
<b>Scores</b>:

<pre>
    sh_score('1/4 c5 d e')
</pre>
returns a `Score` with 3 quarter notes starting at middle C.
Numula's shorthand notation for scores has numerous features
that enable compact representation of complex scores;
for details see [ref].
<p>
The shorthand notations all have a common set of features
that increase their expressive power:
<p>
<b>Iteration</b>:
a string surrounded by `*N ... *` is repeated N times.
For example,
<pre>
    sh_tempo('*4 60 8/4 80 p.03 4/4 60 *')
</pre>
returns a PFT with 4 copies of the above tempo function.
This construct can be nested.
<p>
<b>Parameterization</b> (using the Python f-string feature):
PFT parameters can be variables rather than hard-coded constants.
For example:
<pre>
    med = 60
    faster = 80
    sh_tempo(f'{med} 8/4 {faster} 4/4 {med}')
</pre>
<p>
The contents of the `{}` can be any expression,
including a shorthand notation string:
<pre>
    dv1 = .7
    vmeasure = f'*2 0 1/4 {dv1} 1/4 0'
    pft = sh_vol(f'{vmeasure} 0 1/1 0 {vmeasure}')
</pre>
<p>
<b>Measure checking</b>:
a shorthand string can contain \"measure markers\"
which are used to error-check timing.
For example:
<pre>
    sh_vol('
        |1 pp 2/4 mp 2/4 pp
        |2 ...
    ')
</pre>
When the `|2` is reached, the shorthand interpreter
checks that one measure of time has passed in the PFT definition.
(The length of measures, 4/4 in this case, is configurable.)
This facilitates finding timing errors in long PFT definitions.

<h3>10.2 Interactive parameter adjustment</h3>
<p>
The original low-level editing cycle in Numula was cumbersome;
each adjustment required locating and editing a value in the source code,
re-running the Python program,
and moving the Pianoteq playback pointer to the relevant time.
This took a dozen or so input events (keystrokes and mouse clicks).
<p>
To streamline low-level editing, Numula provides a feature called
<i>Interactive Parameter Adjustment</i> (IPA)
that reduces the cycle to two keystrokes.
In IPA, you declare certain variables to be adjustable,
and specify their role (tempo, volume, and so on).
You then run the program under an <i>IPA interpreter</i>.
The interpreter lets you specify start and end times for playback.
You can select an adjustable variable,
change its value with up and down arrow keys,
and press the space bar to play the selected part of the piece.
This reduces the editing cycle to two keystrokes.
The values of adjustable variables are stored in a file,
which is read when the IPA interpreter is started.

<h2>11. Examples</h2>
<p>
We used Numula to create nuanced renditions
of piano pieces from several styles and periods:
<ul>
<li> Sonata opus 57 by Beethoven, 3rd movement (1804-1805).
<li> Prelude no. 5 by Chopin (1838-1839).
<li> wasserklavier from Six Encores by Luciano Berio (1965).
<li> Three Homages by Robert Helps (1972).
</ul>
<p>
The sound files and source code are at
https://github.com/davidpanderson/numula/wiki#examples
<p>
We used Numula shorthand strings for both score and nuance.
The source code lines counts, and the number of notes in each piece,
are given in Figure 6.
<center>
<table><tr><td>
<pre>
Work        Lines (score)       Lines (nuance)      # of notes
--------------------------------------------------------------
Beethoven   319                 481                 7012
Chopin      148                 161                 1558
Berio       83                  101                 394
Helps #1    126                 163                 1147
Helps #2    94                  99                  1470
Helps #3    116                 87                  1389
</pre>
</td></tr></table>
<p>
<b>Figure 6: Source code sizes for Numula examples.</b>
</center>
<p>
We tried to approximate performances by a skilled human,
and were at least partly successful.
MNM and Numula co-evolved with these examples;
we added new features and notations as needed for the pieces.
<p>
The examples use the general structure described above:
layered transformations for short-, medium- and long-term nuance gestures
in both tempo and dynamics.
We were surprised by the importance of pauses in tempo nuance;
to articulate phrase structure at different levels,
we made widespread use of pauses in the 10 to 100 millisecond range,
before and/or after the beat.
We expressed pauses in transformation separately from tempo variation.

<h2>12. Nuance inference</h2>
<p>
So far we have focused on <i>nuance specification</i>:
how to create nuance descriptions that, when applied to a score,
produce a desired rendition.
We now consider the inverse problem:
'inferring' nuance from a performance.
That is, given a score and a performance of the score
(represented as a list of note and pedal events)
finding a nuance description which, when applied to the score,
closely approximates the performance.
Here we present some ideas on how to do this,
and on the possible applications.
<p>
Given a performance $ P $, there are nuance descriptions
that exactly reproduce $ P $
by specifying the timing and volume of each note and pedal application.
These descriptions are not useful for our purposes.
If $ P $ has a crescendo, we want to represent it as a single entity.
In essence, we seek the simplest nuance description that yields $ P $.
<p>
To formalize this,
we need a notion of the <i>complexity</i> $ C(D) $ of a nuance description $ D $.
$ C(D) $ could perhaps be defined in terms of
the numbers of PFT primitives, transformations, and tags in $ D $.
Given nuance scripting,
it could be defined as the Kolmogorov complexity of $ D $,
i.e. the length of the shortest program that generates $ D $.

<p>
We also need a measure of how closely a nuance description $ D $
matches a performance $ P $.
Suppose a score $ S $ and a performance $ P $ are given.
Let $ N(S, D) $ denote the result of applying $ D $ to $ S $;
like $ P $, it is a list of note and pedal events.
Let $ E(P, N(S, D)) $ be a measure of the difference
between the two event lists.
This could perhaps be based on the root-mean-square (RMS)
of the differences in the inter-event times and in the volumes
of corresponding notes.

<p>
Given the functions $ C $ and $ E $, possible goals are:
<ul>
<li>
Given an error limit $ \ov E\ $,
find $ D $ for which

$ E(P, N(S, D)) < \ov E\ $

and for which $ C(D) $ is minimal, or

<li>
Given a complexity limit $ \ov C\ $,
find $ D $ for which $ C(D) < \ov C\ $
and for which $ E(P, N(S, D)) $ is minimal.

</ul>
<p>

<h3>12.1 Inferring nuance from one performance</h3>
<p>
The above discussion clarifies what we seek:
a simple nuance description $ D $ that matches a performance $ P $.
How can we find one?
We now sketch a crude manual approach as a starting point.
<p>
Intuitively, it seems best to work from long to short:
to identify phrase-level features, then measure-level, then single notes.
So, to describe volume, we might:
<p>
<ul>
<li> Identify a section of $ P $ where the overall volume trends up or down.
<li> Find the primitive type (e.g. linear or exponential)
that best fits the volume contour,
and find the best-fit (e.g. least-squares) parameters
<li> Continue, finding more such disjoint sections,
and assembling the resulting primitives into a PFT.
<li> Subtract this volume transformation from $ P $, leaving a residue.
<li> Fit shorter (beat- or measure-level) primitives, in a similar way,
to the residue.
<li> From the resulting residue, fit accents or patterns of accents.
</ul>
<p>
In addition to describing overall volume,
we might need to use tags to isolate voices or other parts,
and fit their volume in separate transformations.
<p>
These processes might be manual, automated, or a combination.
We might manually find the endpoints of a crescendo,
then let the computer choose the best combination of
primitive type and parameters.
<p>
We could analyze timing nuance in a similar way:
fitting long tempo primitives,
then shorter primitives, then pauses and time shifts.
<p>
The above assumes that we know the correspondence of notes between
performance and score;
this might be hard to find because of mistakes
and missing or extra notes in the performance.

<h3>12.2 Inferring nuance from a set of performances</h3>
<p>
Some applications involve comparing the nuance
of several performances $ P_1,... P_n $ of a given work.
If we infer their nuances separately
(for example, using the above method)
the results will in general be incomparable;
they'll have different nuance structure,
different tagging, and so on.
<p>
In this case we want a set of nuance descriptions $ D_1,... D_n $
all with the same nuance structure
(i.e. the same tagging
and the same sequence of transformation types and note selectors,
but with different PFTs),
and for which $ D_i $ approximates $ P_i $.
<p>
A possible approach to this problem:
<ol>
<li> Generate a nuance description $ D $ for $ P_1 $.
<li> For each $ P_2,...P_n $, generate a nuance description
$ D_i $ that has the same structure as $ D $
and that approximates $ P_i $.
<li> For each $ P_i $ compute the error $ E_i = E(D_i, P_i) $
<li> If all $ E_i $ are below a target level $ \ov E\ $, stop.
<li> Let $ i $ be such that $ E_i $ is greatest.
Examine the residual timing and volume errors.
Add transformations to $ D_i $ that reduce $ E_i $ to less than $ \ov E\ $,
and let $ D = D_i $.
<li> Go to step 2.
</ol>

<h3>12.3 Applications of nuance inference</h3>
<p>
One application of nuance inference is performance style analysis.
In the most general form
this would involve assembling performances of
a number of works $ W_i $ (perhaps from various styles, eras, and countries)
played by a number of performers $ A_i $ (perhaps from different times,
countries, etc.).
For each work $ W_i $, generate a set of comparable nuance descriptions,
one per performer.
By comparing these we can study differences in performance style
between the performer's time period,
nationality, conservatory attended, age, sex, and so on.
<p>
Alternatively, we could infer the nuance of a number of performances
by a particular performer,
and look for characteristic properties in the nuance,
perhaps varying according to the style of the work
or across the lifetime of the performer.

<p>
A second application is the study of PFT primitives.
We have discussed linear and exponential primitives,
but there are many other possibilities:
polynomial, trigonometric, and logarithmic functions, spline curves, etc.
Ideally, MNM should offer a small \"basis set\" of functions,
each with a small number of parameters, that together can
approximate the nuance of a wide range of human performers.
<p>
The process of nuance inference
may reveal situations where existing primitives
don't closely fit a nuance gesture.
We can then look for a function
(perhaps of one of the above types) that does.
<p>
We can also study the extent to which primitive types
are used in different situations.
Different primitives may tend to be used
for long versus short nuance gestures.
The ideal set of primitives may depend on
the period of the performance,
the period and style of the composition,
the individual performer, and so on.

<h2>13. Related work</h2>
<p>
Prior work related to GMN can be divided into several areas.
<p>
<b>Timing:</b>
Rogers and Rockstroh (1980) formalized the meaning of continuous tempo change.
They defined \"clock factor\" (what we call inverse tempo)
and observed that the real time between two events depends
on the integral of this between the two score times.
They worked out the mathematics of three tempo functions:
linear, hyperbolic functions of the form $ F(t) = A/{B-t} $,
and exponential (which they call \"equal ratios\"):
functions of the form $ F(t) = A^t $.
<p>
Several researchers (Dannenberg, Honing 2005)
have identified and proposed solutions to the \"vibrato problem\":
the timing of some musical features
(vibrato, or in our context things like trills and octave tremolos)
should not be affected by tempo changes.
The distinction between tempo and time-shifting is
articulated in (Honing 2001).

<p>
<b>Software systems</b>.
Various music programming languages have included nuance features:
examples include
FORMULA (Anderson 1991), SuperCollider (McCartney 2002),
HMSL (Polansky 1990), and Max (Puckette 2002).
Score editors such as MuseScore and Sibelius have basic nuance features:
you can put a crescendo mark into a score,
and the program's playback feature will play a crescendo.
They also have features for adding algorithmic nuance,
such as a 'swing' rhythm.
These systems offer basic nuance capabilities,
but they lack MNM's ability to represent complex nuance,
and Numula's ability to express it concisely and edit it efficiently.
<p>
Music21 (Cuthbert) and Abjad (https://abjad.github.io/)
are Python-based systems for score representation.
Like Numula, they offer shorthand notations.
However, their goals (musical analysis and typesetting respectively)
are different.

<p>
<b>Studies of nuance in human performances</b>.
Bruno Repp did statistical studies of nuance:
for example, looking at several performances of
a particular section of a piece,
viewing the inter-event times or note volumes
as sets of data points,
and analyzing them using principal component analysis and other tools.
(Repp 1998a, 1998b)
His goals were
to quantify the stylistic differences between different performers,
or between different groups of performers (periods, nationalities, etc.).
Other research has tried to characterize common nuance gestures &mdash;
in particular phrase-ending ritardandos.
Several projects characterized these as a linear tempo change,
and linked this to the uniform slowing of a human pace (Friberg 1999).
<p>
<b>Generating nuance algorithmically</b>.
Other projects have developed algorithms
intended to generate plausible timing and volume nuance for a score,
based on a structural analysis of the score
(a division into sections and subsections) and on its pitch contours
(Friberg 1991).
Todd (1992) studied the relationship between tempo and dynamics.
This area was surveyed by Kirke and Miranda (2009)
and by Cancino-Chacón et al (2018).
The results are plausible but simplistic:
volume increases with pitch, tempo increases with volume,
phrases slow down at the end, and so on.
They typically lack the variety, complexity and detail
of performances by advanced human pianists.

<p>
<b>Cascading Style Sheets</b>.
There is an analogy between MNM and Cascading Style Sheets (CSS),
a system for specifying the appearance of web pages (Lie and Bos, 1997)
Like MNM,
a) a CSS specification is typically separate from the web page;
b) CSS files can be \"layered\":
they are applied in a particular order,
and later files can extend or override the effects of earlier ones;
c) CSS specifications can refer to subsets of the HTML elements
using 'selectors' involving element names, classes, and IDs;
d) CSS preprocessors like SASS (Mazinanian 2016)
have variables and expressions, similar to nuance scripting.

<h2>14. Future work</h2>
<p>
Beyond the areas already discussed,
there are several possible directions for future work involving MNM.
<p>
<b>Extending the model</b>.
The MNM model was designed to handle the pieces listed earlier.
As MNM is used for more works, in a range of styles,
extensions to the model will undoubtedly be needed.
In particular, MNM could be extended to handle the 'vibrato problem'
described above,
involving trills and other ornaments where the number and timing of notes
is not fixed in the score.
For example, if the real-time rate of trill notes is fixed,
then the number of notes in the trill varies with tempo.
The current MNM model does not handle this; doing so would require
evaluating the timing nuance to determine the duration of the trill,
then generating the notes in the trill,
which could be subject to further tempo adjustment.
<p>
MNM could also be extended to describe note parameters
other than duration and initial pitch and volume.
These might include attack parameters (such as bow weight)
and variation in pitch, timbre or volume during a note;
the latter could be modeled as PFTs.
MNM could be extended to handle works with multiple instruments.
Note tags could include the instrument type
(e.g. 'violin') and instance (e.g. 'violin 1').

<p>
<b>Integration with music software systems</b>.
MNM could be integrated with 
score editors such as MuseScore (https://musescore.com),
music analysis systems like Music21 (Cuthbert 2010)
and music languages such as SuperCollider.
To allow interoperability between these systems,
it may be useful to develop a JSON-based file format
for MNM nuance descriptions.


<h2>15. Conclusion</h2>
<p>
We have presented Music Nuance Model (MNM),
a framework for describing nuance in renditions of keyboard works.
MNM is implemented in Numula,
a Python-based system for describing both scores and nuance.
Using Numula or other system supporting MNM,
a musician can create a rendition of a work (perhaps their own composition)
that matches their conception of it.
They can play the result using
a digital synthesizer or computer-controlled physical instrument.
<p>
We used MNM and Numula to create renditions of several advanced piano pieces,
which we had previously learned to play on the (physical) piano.
We found that it was fairly easy to get a plausible rendition,
but progressing beyond that quickly became difficult.
Complex nuance descriptions can have hundreds of components and parameters.
Once the effort of editing the nuance
outweighed the pleasure in making progress,
we tended to stop working on it.
<p>
Therefore we gave considerable thought to nuance editing interfaces.
The easier the interface is to use
&mdash; especially for small-scale details &mdash;
and the more direct its connection to the music,
the more time users will invest in the rendition,
and the musically better the result will be.
<p>
Numula has features (IPA and shorthand notations) that streamline
the editing process and that largely eliminate the need to program.
We think that these features
take the textual approach about as far as it can go.
Making detailed nuance editing feasible for the majority of musicians, we think,
will require a graphical interface extending a score editor,
possibly augmented with textual scripting tools.
The idea of \"demonstrative\" interfaces should also be explored.

<p>
Richard Kraft encouraged this work and contributed ideas
about UI design and the applications of MNM.

<h2>References</h2>
<p>
<ol>
<li> Anderson, D.P., and R.J. Kuivila. 1991.
\"FORMULA: a Programming Language for Expressive Computer Music\",
<i>IEEE Computer</i> 24(7), pp 12-21. June 1991.

<li>Bilson, Malcolm. 2005.
\"Knowing the score: do we know how to read Urtext editions and how can this lead to expressive and passionate performance?\" (video).
<i>Cornell University Press</i>.
YouTube: https://youtu.be/mVGN_YAX03A

<li>
Cancino-Chacón, C., M. Grachten, W. Goebl, G. Widmer.
\"Computational Models of Expressive Music Performance: A Comprehensive and Critical Review\".
<i>Frontiers in Digital Humanities</i>, October 2018.

<li>
Cuthbert, M.S. and A. Christopher.  2010.
\"music21: A Toolkit for Computer-Aided Musicology and Symbolic Music Data.\"
<i>11th International Society for Music Information Retrieval Conference</i>,
August 9-13 2010, Utrecht, Netherlands. pp. 637-642.

<li> Dannenberg, R. 1997.
\"Time Warping of Compound Events and Signals\".
<i>Computer Music Journal</i> 21(3) Autumn 97 pp 61-70.

<li>
Friberg, A.
\"Generative Rules for Music Performance: A Formal Description of a Rule System\".
<i>Computer Music Journal</i> 15(2) summer 1991.

<li>
Friberg, A. and J. Sundberg. 1999.
\"Does music performance allude to locomotion? A model of final ritardandi derived from measurements of stopping runners\".
<i>The Journal of the Acoustical Society of America</i>
105(3):1469-1484.  March 1999.

<li>
Honing, Henkjan. 2001.
\"From Time to Time: The Representation of Timing and Tempo\".
<i>Computer Music Journal</i>, Vol. 25, No. 3 (Autumn, 2001), pp. 50-61.

<li>
Honing, Henkjan. 2005.
\"The Vibrato Problem: Comparing Two Solutions\".
<i>Computer Music Journal</i> 19(3) Autumn 2005.

<li>
Kirke, A. and E. Miranda.
\"A Survey of Computer Systems for Expressive Music Performance\".
<i>ACM Computing Surveys</i> 42(1). December 2009.

<li>
Mazinanian, D. and N. Tsantalis. 2016.
\"An Empirical Study on the Use of CSS Preprocessors\",
<i>IEEE 23rd International Conference on Software Analysis, Evolution, and Reengineering</i>, Osaka, Japan, pp. 168-178.
<li>
Lie, Håkon & Bos, Bert. 1997. Cascading style sheets.
<i>World Wide Web Journal</i> 2. 75-123. 

<li> McCartney, J. 2002.
\"Rethinking the Computer Music Language: SuperCollider\".
<i>Computer Music Journal</i>
Vol. 26, No. 4, (Winter, 2002), pp. 61-68

<li>Nienhuys, H-W, and J. Nieuwenhuizen.
\"LilyPond, a system for automated music engraving.\"
<i>Proceedings of the xiv colloquium on musical informatics</i>. Vol. 1. Firenza: Tempo Reale, 2003.

<li>
Polansky, L., P. Burk and D. Rosenboom. 1990.
\"HMSL (Hierarchical Music Specification Language): A Theoretical Overview\".
<i>Perspectives of New Music</i>
Vol. 28, No. 2 (Summer, 1990), pp. 136-178 

<li>
Puckette, Miller. 2002.
\"Max at Seventeen\".
<i>Computer Music Journal</i> 26(4): pp. 31-43.

<li> Repp, B. 1998.
\"A microcosm of musical expression. I. Quantitative analysis of pianists’ timing in the initial measures of Chopin’s Etude in E major\".
<i>The Journal of the Acoustical Society of America</i>, 1998

<li> Repp, B. 1998.
\"A microcosm of musical expression. I. Quantitative analysis of pianists’ timing in the initial measures of Chopin’s Etude in E major\".
<i>The Journal of the Acoustical Society of America</i>, 1998.

<li>
Resnick, M. et al. 2009.
\"Scratch: Programming for All\". 2009.
<i>Communications of the ACM</i>
52(11), pp. 60-67.

<li>
Rogers, J. and J. Rockstroh. 1980.
\"Music-Time and Clock-Time Similarities under Tempo Change\".
<i>Proceedings of The 1980 International Computer Music Conference</i>.

<li>
Todd, Neil.  1992.
\"The dynamics of dynamics: A model of musical expression\".
<i>The Journal of the Acoustical Society of America</i>.
91, 3540 (1992)

</ol>
</div>
</body>
</html>
";

echo expand($text);
?>
