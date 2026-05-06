# Workflow Reflection

## a) Setup

I used ChatGPT as an AI assistant during the implementation. We built the project structure together, defined the main responsibilities of the driver components, and implemented the required functionality step by step with tests. The work was focused on the required subset of the protocol: frame building, response parsing, BCC validation, status byte decoding, GetStatus, sale flow, and printer error handling. I reviewed the generated code, adjusted the Laravel structure, and checked that the implementation remained small, readable, and testable instead of turning it into a larger SDK.

## b) Hallucinations and corrections

I did not observe a serious AI hallucination in the final implementation where the assistant invented a protocol rule and I accepted it as correct. I treated all AI output as a draft and verified protocol-sensitive parts against `PROTOCOL_SPEC.md` before keeping them.

## c) Me vs agent

For the byte-level protocol implementation, I relied heavily on the AI assistant because I do not have deep previous experience with fiscal printer protocols or low-level byte framing. 